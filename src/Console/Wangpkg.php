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
use Wang\Pkg\Services\SwooleServices;


class Wangpkg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg {action?} {param?} {param1?} {param2?}';

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

    public function createModel($tabName)
    {
        ManageDB::addModel($tabName, true, true);
        print_r("create ok! \n");
    }



    //普通队列版本   协程队列版本
    //php artisan wangpkg queueMaster
    public function queueMaster()
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

    //php artisan wangpkg queueSlave
    public function queueSlave($taskName)
    {
        if (!$taskName) {
            echo "请传入队列名称";
            exit;
        }

        //防止redis过期

        while (true) {
            $rd = $this->getRd();
            $queueJson = $rd->lPop('queue_' . $taskName);
            $rd->close();
            if ($queueJson) {
                try {
                    $queue = json_decode($queueJson, true);
                    $taskName = ucfirst(\Wang\Pkg\Lib\Util::camelize($queue['taskname']));
                    $filePath = app_path('Action/' . $taskName . '.php');
                    if (is_file($filePath)) {
                        //判断执行方法是否存在
                        if (method_exists("\App\QueueAction\\$taskName", "run")) {
                            $actionName = "\App\QueueAction\\$taskName::run";
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
                    echo $e->getLine() . $e->getMessage();
                } catch (Error $e) {
                    echo $e->getLine() . $e->getMessage();
                } finally {
                    //finally是在捕获到任何类型的异常后都会运行的一段代码
                }
            } else {
                usleep(50000);
            }
        }

        //观察者处理事件
        //读取redis队列  反射执行自定义run方法  传入$queue的Model对象
        //在run方法执行完成后修改队列的状态   做好异常处理
    }

    //不管该任务是什么状态都执行
    //php artisan wangpkg execute 123
    public function executeShell($queueId)
    {
        if ($queueId) {
            $queue = Queue::where('id', $queueId)->first();
            $taskName = ucfirst($queue->taskname);
            $actionName = "\App\QueueAction\\$taskName::run";

            //不然可能整个脚本崩溃
            //抛出错误 提示没有run方法  这里最好用xShell执行  只传id进去  继承Action对象   在Action对象中把QueueModel查询出来
            //method_exists("\App\QueueAction\\$taskName::class","run");

            //判断文件是否存在
            $filePath = app_path('Action/' . $taskName . '.php');

            if (is_file($filePath)) {
                $queue->state = 3;
                $queue->save();
                $result = $actionName($queue);

                var_dump($result);

                if ($result == true) {

                    $queue->state = 4;
                    $queue->save();
                } else {
                    //执行发生错误
                    $queue->state = 5;
                    $queue->save();
                }
            } else {
                //执行脚本异常
                $queue->state = 7;
                $queue->save();
                var_dump('为发现该执行方法');
            }

        }
    }

    //php artisan wangpkg swooleQueue
    public function swooleQueue($param)
    {
        $queueId = $this->argument('param');

        if(!$queueId){
            $queueId = 1;
        }

        $param1= $this->argument('param1',1);
        $param2 = $this->argument('param2');

        $q = \App\Models\Queue::find($queueId);

        if(!$q){
            echo "未找到该队列任务";
        }

        $q = $q->toArray();
        $taskName = $q['taskname'];

        \Swoole\Runtime::enableCoroutine();

        $config = SwooleServices::getConfig();

        \Co\run(function () use ($q, $config,$taskName) {
            //实例化redis连接池
            $redisPool = SwooleServices::getRedisPool($config);

            //实例化pdo连接池
            $pdoPool = SwooleServices::getPdoPool($config);

            go(function () use ($q, $pdoPool, $redisPool,$taskName) {
                $taskName = ucfirst(\Wang\Pkg\Lib\Util::camelize($taskName));
                $actionName = "\App\QueueAction\\$taskName::run";
                //$queue['content'] = json_decode($queue['content'],true);
                $result = @$actionName($q, $pdoPool, $redisPool);
            });

        });

    }


    public function defaultRun()
    {
        echo "未找到执行方法";
    }


}
