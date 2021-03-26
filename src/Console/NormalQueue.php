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
        $rd = new \Redis();

        $rd->connect($_config['hostname'], $_config['port']);
        $rd->auth($_config['password']);
        $rd->select(0);
        //防止超时 https://blog.csdn.net/qmhball/article/details/52575133  分析超时  strace php sub.php
        $rd->setOption(\Redis::OPT_READ_TIMEOUT, -1);

        return $rd;
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
        //防止redis过期
        $rd = $this->getRd();
        while (true) {
            try {
                //查询为处理的   改为处理中
                $Queues = Queue::where('state', 1)->get(['id', 'ulid', 'taskname', 'content']);

                foreach ($Queues as $queue) {
                    //循环写入redis队列
                    $rd->rPush('queue_' . $queue->taskname, json_encode($queue->toArray()));
                    $queue->state = 2;
                    $queue->save();
                }
            } catch (\Exception $e) {

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
    }

    //php artisan wangpkg:queue slave
    public function slave($taskName)
    {
        if (!$taskName) {
            $taskName = 'normal';
        }

        //防止redis过期
        $rd = $this->getRd();
        while (true) {

            $queueJson = $rd->lPop('queue_' . $taskName);
            if ($queueJson) {
                try {
                    $queue = json_decode($queueJson, true);
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

                } catch (\Exception $e) {
                    echo $e->getLine().$e->getMessage();
                } catch (Error $e) {
                    echo $e->getLine().$e->getMessage();
                } finally {
                    //finally是在捕获到任何类型的异常后都会运行的一段代码
                }
            } else {
                usleep(50000);
            }
        }
    }



    public function test(){
        while (true) {
            QueueServices::add(['taskname'=>'test']);
        }
    }

    public function defaultRun()
    {
        echo "未找到执行方法";
    }


}
