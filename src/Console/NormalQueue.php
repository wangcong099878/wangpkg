<?php

namespace Wang\Pkg\Console;


use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Wang\Pkg\Lib\EasyRedis;
use Wang\Pkg\Lib\ManageDB;
use App\Models\Queue;
use App\Models\QueueHistory;
use Wang\Pkg\Lib\Shell;
use Wang\Pkg\Services\QueueServices;
use Wang\Pkg\Lib\Log;

class NormalQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg:queue {action?} {param?} {param1?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $action = $this->argument('action');

        if ($action == '') {
            echo "请输入操作名！";
            return;
        }

        $param = $this->argument('param');
        try {
            if (method_exists($this, $action)) {
                call_user_func([$this, $action], $param);
            } else {
                //定义别名
                switch ($action) {
                    case 'cm':
                        $this->createModel($param);
                        break;

                    default:
                        $this->defaultRun($param);

                }
            }

        } catch (\Exception $e) {

        }

    }

    //
    public function getRd()
    {
        $_config = array(
            'hostname' => env('REDIS_HOST'),
            'port' => env('REDIS_PORT'),
            'password' => env('REDIS_PASSWORD'),
        );
        try{

            redis_connect:
            $rd = new \Redis();

            //$rd->connect($_config['hostname'], $_config['port']);
            $rd->pconnect($_config['hostname'], $_config['port']);
            $rd->auth($_config['password']);
            $rd->select(0);
            //防止超时 https://blog.csdn.net/qmhball/article/details/52575133  分析超时  strace php sub.php
            $rd->setOption(\Redis::OPT_READ_TIMEOUT, -1);

            return $rd;
        }catch (\Throwable $e) {
            Log::showMsgLog('redis准备重连','redis');
            Log::showErrLog($e);

            sleep(3);
            goto redis_connect;
        }

    }

    //转移成功数据到历史表 php artisan wangpkg transferSuccess
    public function transferSuccess()
    {
        $Queues = Queue::where('state', 7)->get();
        foreach ($Queues as $queue) {
            $data = $queue->toArray();
            unset($data['id']);
            var_dump(QueueHistory::insert($data));
            var_dump($queue->delete());
        }

    }

    //php artisan wangpkg:queue master
    public function master()
    {
        try {
            //防止redis过期
            $rd = $this->getRd();
            while (true) {
                try {
                    shell_restart:
                    //查询为处理的   改为处理中
                    $Queues = Queue::where('state', 1)->get(['id', 'ulid', 'taskname', 'content']);

                    foreach ($Queues as $queue) {
                        //循环写入redis队列
                        $rd->rPush('queue_' . $queue->taskname, json_encode($queue->toArray()));
                        $queue->state = 2;
                        $queue->save();
                    }
                } catch (\Throwable $e) {
                    Log::showMsgLog('发生异常准备重启脚本','shell');
                    Log::showErrLog($e);

                    sleep(3);
                    $rd = $this->getRd();
                    goto shell_restart;
                }
                //sleep(1);
                //sleep for 5 seconds
                //usleep(5000000);
                //500 毫秒
                //usleep(500000);
                //100毫秒
                //usleep(100000);
                usleep(50000);
            }
        } catch (\Throwable $e) {
            Log::showMsgLog('脚本终止','mysql');
            Log::showErrLog($e);
            exit(404);
        } finally {
            sleep(3);
        }
    }

    //php artisan wangpkg:queue slave
    public function slave($taskName)
    {
        if (!$taskName) {
            $taskName = 'normal';
        }
        try {
            shell_restart:
            //防止redis过期
            $rd = $this->getRd();
            while (true) {
                //出队不能使用 rPop，lPop，因为这两个方法是个长连接，一直连着Redis，redis报错如下：  那就使用 brPop，blPop
                $queueJson = $rd->blPop('queue_' . $taskName, 10);
                if ($queueJson) {
                    $this->runQueue($queueJson);
                }
            }
        } catch (\Throwable $e) {
            Log::showMsgLog('脚本终止','mysql');
            Log::showErrLog($e);
            goto shell_restart;
            sleep(3);
            exit(404);
        } finally {
            sleep(3);
        }
    }

    //此处必须拎出来
    public function runQueue($queueJson)
    {
        try {
            $queue = json_decode($queueJson[1], true);

            $taskName = ucfirst(\Wang\Pkg\Lib\Util::camelize($queue['taskname']));
            $filePath = app_path('QueueAction/' . $taskName . '.php');
            if (is_file($filePath)) {
                //判断执行方法是否存在
                if (method_exists("\App\QueueAction\\$taskName", "run")) {
                    $actionName = "\App\QueueAction\\$taskName::run";

                    //$queue['content'] = json_decode($queue['content'],true);

                    $result = @$actionName($queue);
                } else {
                    $result = "执行方法run不存在:" . $filePath;
                }
            } else {
                $result = "执行脚本不存在:" . $filePath;
            }

            if (!$result) {
                $result = 'null';
            }

            $pdo = DB::connection()->getPdo();
            //执行成功
            if ($result == "success") {
                $sql = "UPDATE `queue` SET `state`=:state,`error_reason`=:error_reason WHERE `ulid`=:ulid";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':state' => 5, ':ulid' => $queue['ulid'], 'error_reason' => $result));
                //echo $stmt->rowCount();

            } else {
                $stmt = $pdo->prepare("UPDATE `queue` SET `state`=:state,`error_reason`=:error_reason,`error_num`=error_num+1 WHERE `ulid`=:ulid");
                $stmt->execute(array(':state' => 6, ':ulid' => $queue['ulid'], 'error_reason' => $result));
                //echo $stmt->rowCount();

                $stmt = $pdo->prepare("INSERT INTO `queue_error` (`taskname`,`ulid` ,`error_reason`,`created_at`,`updated_at`)VALUES (:taskname,:ulid, :error_reason,:created_at,:updated_at)");
                $date = date('Y-m-d H:i:s');
                $stmt->execute([
                    ':taskname' => $queue['taskname'],
                    ':ulid' => $queue['ulid'],
                    ':error_reason' => $result,
                    ':created_at' => $date,
                    ':updated_at' => $date,
                ]);

                //错误重试
                $stmt = $pdo->prepare("SELECT state,error_num FROM `queue` WHERE `ulid`=:ulid");
                $stmt->execute([':ulid' => $queue['ulid']]);

                $datas = $stmt->fetchAll(2);
                if ($datas[0]['error_num'] < 5) {
                    //延迟3秒重试
                    usleep(50000);
                    $sql = "UPDATE `queue` SET `state`=:state WHERE `ulid`=:ulid";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array(':state' => 1, ':ulid' => $queue['ulid']));
                }

            }

        } catch (\Throwable $e) {
            Log::showMsgLog('执行队列发生错误');
            Log::showErrLog($e);
        } finally {
            //finally是在捕获到任何类型的异常后都会运行的一段代码
        }
    }

    public function test()
    {
        while (true) {
            QueueServices::add(['taskname' => 'test']);
        }
    }

    public function defaultRun()
    {
        echo "未找到执行方法";
    }


}
