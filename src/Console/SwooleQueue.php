<?php

namespace Wang\Pkg\Console;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Wang\Pkg\Lib\EasyRedis;
use Wang\Pkg\Lib\ManageDB;
use App\Models\Queue;
use App\Models\QueueHistory;
use Wang\Pkg\Lib\xShell;

use Wang\Pkg\Services\SwooleServices;

use Swoole\Runtime;
use Swoole\Coroutine;


use Wang\Pkg\Lib\Swoole\RedisConfig;
use Wang\Pkg\Lib\Swoole\RedisPool;

use Wang\Pkg\Lib\Swoole\PDOConfig;
use Wang\Pkg\Lib\Swoole\PDOPool;

use Wang\Pkg\Lib\Log;

class SwooleQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg:swoole_queue {action?} {param?} {param1?} {param2?}';

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


        //https://blog.csdn.net/weixin_30426957/article/details/95896317  这里改造成反射传参
        //print_r($this->arguments());

        $param = $this->argument('param');
        $param1 = $this->argument('param1');
        try {
            if (method_exists($this, $action)) {
                //call_user_func([$this, $action], [$param,$param1]);
                call_user_func_array([$this, $action], [$param, $param1]);
            } else {
                //
                $this->defaultRun($param);
            }

        } catch (\Exception $e) {

        }

    }


    //普通队列版本   协程队列版本
    //php artisan wangpkg:swoole_queue xMaster
    public function xMaster()
    {
        Runtime::enableCoroutine();

        $config = SwooleServices::getConfig();

        \Co\run(function () use ($config) {

            //实例化redis连接池
            $redisPool = SwooleServices::getRedisPool($config);

            //实例化pdo连接池
            $pdoPool = SwooleServices::getPdoPool($config);

            //实例化
            //$masterWorker = new \chan(2);
            try {
                //读取数据库信息   写入redis队列
                while (true) {
                    try {
                        $mysql_connect_num = 1;
                        mysql_connect:
                        $mysql_connect_num++;

                        if ($mysql_connect_num > 10) {
                            Log::showMsgLog('断线重连10次，强制退出');
                            exit(404);
                        }

                        $pdo = $pdoPool->get();
                        $statement = $pdo->prepare("SELECT id,ulid,taskname,content FROM queue where state=? limit 100");
                        $statement->execute([1]);
                        $queues = $statement->fetchAll(2);
                        $pdoPool->put($pdo);
                    } catch (\Throwable $e) {
                        Log::showMsgLog('pdo 重连','mysql');
                        Log::showErrLog($e);
                        //等待50毫秒
                        \Co::sleep(0.05);
                        \Co::sleep(0.05);
                        // 断线重连
                        $pdoPool->put(null);

                        goto mysql_connect;
                    }

                    if ($queues) {
                        $ulidList = [];
                        foreach ($queues as $queue) {
                            //写入redis
                            $queue['content'] = json_decode($queue['content'], true);
                            try {
                                redis_connect:
                                $rd = $redisPool->get();
                                $rd->rPush('queue_' . $queue['taskname'], json_encode($queue));
                                $redisPool->put($rd);
                            } catch (\Throwable $e) {
                                Log::showMsgLog('重连','redis');
                                Log::showErrLog($e);

                                //等待100毫秒
                                \Co::sleep(0.1);
                                // 断线重连
                                $redisPool->put(null);
                                goto redis_connect;
                            }

                            $ulidList[] = $queue['ulid'];
                        }

                        if ($ulidList) {
                            $idStr = implode($ulidList, '\',\'');
                            $pdo->exec("UPDATE `queue` SET `state`=2 WHERE `ulid` IN ('{$idStr}')");
                        }

                    } else {
                        //50毫秒
                        \Co::sleep(0.05);
                    }
                }
            } catch (\Throwable $e) {
                Log::showMsgLog('致命异常终止脚本');
                Log::showErrLog($e);
                sleep(3);
                //exit(404);
            } finally {
                //Log::showMsgLog('结束前处理','finally');
                sleep(3);
            }


        });
    }

    //队列名称 $taskname
    //php artisan wangpkg:swoole_queue xSlave test  全协程操作
    public function xSlave($taskname, $slaveWorkerNum = 10)
    {
        Runtime::enableCoroutine();

        \Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

        if (!$slaveWorkerNum) {
            $slaveWorkerNum = 10;
        }

        if (!$taskname) {
            $taskname = 'swoole';
        }

        $config = SwooleServices::getConfig();

        \Co\run(function () use ($config, $taskname, $slaveWorkerNum) {

            //实例化redis连接池
            $redisPool = SwooleServices::getRedisPool($config);

            //实例化pdo连接池
            $pdoPool = SwooleServices::getPdoPool($config);

            //slave数量
            $slaveWorker = new \chan($slaveWorkerNum);

            //打印工作状态
            /*            go(function () {
                            while (true) {
                                \Co::sleep(1);
                                print_r(\Swoole\Coroutine::stats());
                            }
                        });*/
            try {
                //读取数据库信息   写入redis队列
                while (true) {

                    //读取lpop数据
                    try {
                        redis_connect:
                        $rd = $redisPool->get();
                        $queueJson = $rd->lPop('queue_' . $taskname);
                        $redisPool->put($rd);
                    } catch (\Throwable $e) {
                        Log::showMsgLog('重连','redis');
                        Log::showErrLog($e);
                        $redisPool->put(null);
                        \Co::sleep(0.05);
                        // 断线重连
                        goto redis_connect;
                    }


                    //执行读取到的数据
                    if ($queueJson) {
                        $slaveWorker->push($queueJson);
                        go(function () use ($slaveWorker, $queueJson, $pdoPool, $redisPool, $config) {


                            $pdo = $pdoPool->get();

                            //确保pdo的链接状态
                            //$status = $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS);

                            try {
                                $queue = json_decode($queueJson, true);

                                $taskName = ucfirst(\Wang\Pkg\Lib\Util::camelize($queue['taskname']));

                                $filePath = app_path('QueueAction/' . $taskName . '.php');

                                if (is_file($filePath)) {
                                    //判断执行方法是否存在
                                    if (method_exists("\App\QueueAction\\$taskName", "run")) {
                                        $actionName = "\App\QueueAction\\$taskName::run";

                                        //$queue['content'] = json_decode($queue['content'],true);

                                        $result = @$actionName($queue, $pdoPool, $redisPool);
                                    } else {
                                        $result = "执行方法run不存在:" . $filePath;
                                    }
                                } else {
                                    $result = "执行脚本不存在:" . $filePath;
                                }

                                if (!$result) {
                                    $result = 'null';
                                }

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


                                    //错误重试
                                    $stmt = $pdo->prepare("SELECT state,error_num FROM `queue` WHERE `ulid`=:ulid");
                                    $stmt->execute([':ulid' => $queue['ulid']]);

                                    $datas = $stmt->fetchAll(2);


                                    $stmt = $pdo->prepare("INSERT INTO `queue_error` (`taskname`,`ulid` ,`error_reason`,`try_again`,`error_num`,`created_at`,`updated_at`)VALUES (:taskname,:ulid, :error_reason,:try_again,:error_num,:created_at,:updated_at)");
                                    $date = date('Y-m-d H:i:s');
                                    $stmt->execute([
                                        ':taskname' => $queue['taskname'],
                                        ':ulid' => $queue['ulid'],
                                        ':error_reason' => $result,
                                        ':try_again' => 1,
                                        ':error_num' => $datas[0]['error_num'],
                                        ':created_at' => $date,
                                        ':updated_at' => $date,
                                    ]);

/*                                    $sql = "INSERT INTO `queue_error` (`taskname`,`ulid` ,`error_reason`,`created_at`,`updated_at`)VALUES (:taskname,:ulid, :error_reason,:created_at,:updated_at)";
                                    $stmt = $pdo->prepare($sql);
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
                                        \Co::sleep($config['delay_retrying_time']);
                                        $sql = "UPDATE `queue` SET `state`=:state WHERE `ulid`=:ulid";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute(array(':state' => 1, ':ulid' => $queue['ulid']));
                                    }*/
                                }
                            } catch (\Throwable $e) {
                                Log::showMsgLog('执行队列发生错误');
                                Log::showErrLog($e);
                                //echo "第" . $e->getLine() . "行：" . $e->getMessage() . "\n";
                            } finally {
                                //finally是在捕获到任何类型的异常后都会运行的一段代码
                                $slaveWorker->pop();
                            }

                            $pdoPool->put($pdo);
                        });

                    } else {
                        //50毫秒
                        \Co::sleep(0.05);
                    }

                }

            } catch (\Throwable $e) {
                Log::showMsgLog('循环终止');
                Log::showErrLog($e);
                sleep(3);
                exit(404);
            } finally {
                sleep(3);
            }

        });

    }

    //php artisan wangpkg:queue queueSlave
    public function queueSlave()
    {
        Runtime::enableCoroutine();

        $config = $this->getConfig();

        \Co\run(function () use ($config) {

            //实例化redis连接池
            $redisPool = new RedisPool((new RedisConfig)
                ->withHost($config['redis_host'])
                ->withPort($config['redis_port'])
                ->withAuth($config['redis_password'])
                ->withDbIndex($config['redis_db'])
                ->withTimeout(1)
            );

            //实例化pdo连接池
            $pdoPool = new PDOPool((new PDOConfig)
                ->withHost($config['db_host'])
                ->withPort($config['db_port'])
                // ->withUnixSocket('/tmp/mysql.sock')
                ->withDbName($config['db_database'])
                ->withCharset('utf8mb4')
                ->withUsername($config['db_username'])
                ->withPassword($config['db_password'])
            );

            //slave数量
            $slaveWorker = new \chan($config['slave_woker_num']);

            //打印工作状态
            go(function () {
                while (true) {
                    \Co::sleep(1);
                    print_r(\Swoole\Coroutine::stats());
                }
            });

            //读取数据库信息   写入redis队列
            while (true) {
                $rd = $redisPool->get();

                $queueJson = $rd->lPop($config['queue_redis_key']);

                if ($queueJson) {
                    $slaveWorker->push($queueJson);
                    go(function () use ($slaveWorker, $queueJson, $pdoPool, $config) {
                        $pdo = $pdoPool->get();
                        try {
                            $queue = json_decode($queueJson, true);

                            $result = xShell::execPHP('wangpkg:queue executeShell', $queue);

                            //执行成功
                            if ($result == "success") {
                                $sql = "UPDATE `queue` SET `state`=:state WHERE `ulid`=:ulid";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute(array(':state' => 5, ':ulid' => $queue['ulid']));
                                //echo $stmt->rowCount();

                            } else {
                                $stmt = $pdo->prepare("UPDATE `queue` SET `state`=:state,`error_reason`=:error_reason,`error_num`=error_num+1 WHERE `ulid`=:ulid");
                                $stmt->execute(array(':state' => 6, ':ulid' => $queue['ulid'], 'error_reason' => $result));
                                //echo $stmt->rowCount();

                                $stmt = $pdo->prepare("INSERT INTO `queue_error` (`ulid` ,`error_reason`,`created_at`,`updated_at`)VALUES (:ulid, :error_reason,:created_at,:updated_at)");
                                $date = date('Y-m-d H:i:s');
                                $stmt->execute([
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
                                    \Co::sleep($config['delay_retrying_time']);
                                    $sql = "UPDATE `queue` SET `state`=:state WHERE `ulid`=:ulid";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute(array(':state' => 1, ':ulid' => $queue['ulid']));
                                }

                            }
                        } catch (\Throwable $e) {
                            echo "第" . $e->getLine() . "行：" . $e->getMessage() . "\n";
                        } finally {
                            $slaveWorker->pop();
                        }

                        $pdoPool->put($pdo);
                    });

                } else {
                    //50毫秒
                    \Co::sleep(0.05);
                }

                $redisPool->put($rd);

            }

        });

    }

    //不管该任务是什么状态都执行
    //php artisan wangpkg execute 123
    public function executeShell($param)
    {

        $ulid = '';
        try {
            $paramArr = json_decode(urldecode($param), true);
            $ulid = $paramArr['ulid'];
        } catch (\Exception $e) {
            $paramArr = [];
        }

        if ($ulid) {
            $queue = Queue::where('ulid', $ulid)->first();
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

                echo $result;
                /*                if ($result === "success") {
                                    echo $result;
                                } else {
                                    //执行发生错误
                                    echo '执行发生错误:'.$filePath.$result;
                                }*/
            } else {
                //执行脚本异常
                echo '执行脚本不存在:' . $filePath;
            }

        }
    }


    public function defaultRun()
    {
        //echo "未找到执行方法";
        Runtime::enableCoroutine();

        $config = [
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD')
        ];

        Coroutine\run(function () use ($config) {
            $pool = new PDOPool((new PDOConfig)
                ->withHost($config['host'])
                ->withPort($config['port'])
                // ->withUnixSocket('/tmp/mysql.sock')
                ->withDbName($config['database'])
                ->withCharset('utf8mb4')
                ->withUsername($config['username'])
                ->withPassword($config['password'])
            );


            go(function () use ($pool) {
                $pdo = $pool->get();

                $sql = "SELECT ulid FROM queue where state=? limit 100";
                $statement = $pdo->prepare($sql);

                $statement->execute([1]);

                //FETCH_ASSOC = 2
                $result = $statement->fetchAll(2);


                $ulidList = [];

                foreach ($result as $v) {
                    $ulidList[] = $v['ulid'];
                }


                $idStr = implode($ulidList, '\',\'');
                echo $sql = "UPDATE `queue` SET `state`=2 WHERE `ulid` IN ('{$idStr}')";
                $pdo->exec($sql);
                //$stmt = $dbh->prepare($sql);

                //$stmt->execute(array(':userId'=>'7', ':password'=>'4607e782c4d86fd5364d7e4508bb10d9'));

                //echo $stmt->rowCount();

                print_r($result);

                $pool->put($pdo);
            });

        });

    }

    //php artisan wangpkg taskCount
    public function taskCount()
    {

    }


}
