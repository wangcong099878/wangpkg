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
use Dotenv\Dotenv;

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

    public static function post($url, $data)
    {
        $urlinfo = parse_url($url);
        $port = 80;
        $https = false;
        if (isset($urlinfo['scheme']) && $urlinfo['scheme'] == 'https') {
            $port = 443;
            $https = true;
        }

        if (isset($urlinfo['port'])) {
            $port = $urlinfo['port'];
        }

        $client = new \Swoole\Coroutine\Http\Client($urlinfo['host'], $port, $https);
        $client->post($urlinfo['path'], $data);
        $body = $client->body;
        $client->close();

        return $body;
    }

    public static function get($url)
    {
        $urlinfo = parse_url($url);
        $port = 80;
        $https = false;
        if (isset($urlinfo['scheme']) && $urlinfo['scheme'] == 'https') {
            $port = 443;
            $https = true;
        }
        $query = '';
        if (isset($urlinfo['query'])) {
            $query = '?' . $urlinfo['query'];
        }

        if (isset($urlinfo['port'])) {
            $port = $urlinfo['port'];
        }

        $client = new \Swoole\Coroutine\Http\Client($urlinfo['host'], $port, $https);
        $client->get($urlinfo['path'] . $query);
        $body = $client->body;
        $client->close();

        return $body;
    }

    //SwooleServices::file_put_contents(base_path('./test1.txt'),'123456',FILE_APPEND);
    public static function file_put_contents(string $filename, string $fileContent, $flags = 0)
    {
        return \Swoole\Coroutine\System::writeFile($filename, $fileContent, $flags);
    }


    public static function file_get_contents($filename)
    {
        return \Swoole\Coroutine\System::readFile($filename);
    }

    //composer require vlucas/phpdotenv:3.3
    public static function env()
    {
        $envPath = base_path('.env');
        $content = self::file_get_contents($envPath);

        $arr = explode("\n", $content);

        $result = [];

        foreach ($arr as $v) {
            if (!$v) {
                continue;
            }
            $val = explode("=", $v);
            $result[$val[0]] = $val[1];
        }

        return $result;
    }

}
