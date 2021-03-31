<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2019/04/24
 * Time: 15:20
 */

namespace Wang\Pkg\Services;


use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

class SwooleServices
{
    //获取配置
    public static function getConfig()
    {
        $config = [
            'db_host' => env('DB_HOST'),
            'db_port' => env('DB_PORT'),
            'db_database' => env('DB_DATABASE'),
            'db_username' => env('DB_USERNAME'),
            'db_password' => env('DB_PASSWORD'),
            'master_woker_num' => 2,
            'slave_woker_num' => 40,  //苹果笔记本极限开25个
            'queue_redis_key' => 'wangpkg_queue',
            'redis_host' => env('REDIS_HOST'),
            'redis_password' => env('REDIS_PASSWORD'),
            'redis_port' => env('REDIS_PORT'),
            'redis_db' => 0,
            'delay_retrying_time' => 2
        ];

        return $config;
    }

    public static function getPdoPool($config)
    {

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

        return $pdoPool;
    }

    public static function getRedisPool($config)
    {
        //实例化redis连接池
        $redisPool = new RedisPool((new RedisConfig)
            ->withHost($config['redis_host'])
            ->withPort($config['redis_port'])
            ->withAuth($config['redis_password'])
            ->withDbIndex($config['redis_db'])
            ->withTimeout(1)
        );

        return $redisPool;
    }

}
