<?php

namespace App\QueueAction;
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2021/3/18
 * Time: 5:25 下午
 */

use App\Models\Queue;
use Swoole\Http2\Request;
use Swoole\Coroutine\Http2\Client;

use Swoole\Runtime;
use Swoole\Coroutine;

/*use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;*/

use Wang\Pkg\Lib\Swoole\RedisConfig;
use Wang\Pkg\Lib\Swoole\RedisPool;

use Wang\Pkg\Lib\Swoole\PDOConfig;
use Wang\Pkg\Lib\Swoole\PDOPool;


use Wang\Pkg\Services\SwooleServices;

class Swoole
{

    //如果使用协程版  一些特殊方法  这里也只能使用协程
    //post请求也只能使用 swoole中的
    //独立调试  如果用全协程   name这里不能用laravel中的model  只能使用swoole中的连接池操作数据库
    //单独调试 App\QueueAction\Swoole::test(\App\Models\Queue::find(1)->toArray());
    public static function run(array $q, PDOPool $pdoPool, RedisPool $redisPool)
    {

        //此处为swoole协程中 处理队列信息
        try {
            $pdo = $pdoPool->get();
            var_dump($pdo);
            //var_dump($redisPool);

            $data = $q['content'];
            print_r($q);
            //https://wiki.swoole.com/#/coroutine_client/http_client
            //用这个   https://github.com/swlib/saber
            /*            $url = 'https://105.m.molibx.com/api/task/list';
                        $urlinfo = parse_url($url);

                        $port = 80;
                        $https = false;
                        if (isset($urlinfo['scheme']) && $urlinfo['scheme'] == 'https') {
                            $port = 443;
                            $https = true;
                        }

                        $client = new \Swoole\Coroutine\Http\Client($urlinfo['host'], $port, $https);
                        $client->post($urlinfo['path'], array('a' => '123', 'b' => '456'));
                        var_dump($client->body);
                        $client->close();*/
        } catch (\Throwable $e) {
            echo "第" . $e->getLine() . "行：" . $e->getMessage() . "\n";
        } finally {
            //finally是在捕获到任何类型的异常后都会运行的一段代码,结束之前也一定会执行
            //echo "run 方法执行失败";
            //关闭链接 回收资源 回收连接池  回收wokerchan
            $pdoPool->put($pdo);
        }

        //执行成功返回"success"  错误则返回错误信息
        return "success";
    }


    public static function test($q)
    {
        Runtime::enableCoroutine();

        $config = SwooleServices::getConfig();

        \Co\run(function () use ($q, $config) {
            //实例化redis连接池
            $redisPool = SwooleServices::getRedisPool($config);

            //实例化pdo连接池
            $pdoPool = SwooleServices::getPdoPool($config);

            go(function () use ($q, $pdoPool, $redisPool) {
                self::run($q, $pdoPool, $redisPool);
            });

        });
    }
}
