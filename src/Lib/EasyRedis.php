<?php

/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2018/4/16
 * Time: 上午11:38
 */

namespace Wang\Pkg\Lib;

class EasyRedis
{

    protected static $_instance;
    protected static $_config;

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    public static function initConfig()
    {
        //dev配置
        self::$_config = array(
            'hostname' => env('REDIS_HOST'),
            'port' => env('REDIS_PORT'),
            'password' => env('REDIS_PASSWORD'),
        );
    }

    public static function getInstance($db = 0, $is_new = false)
    {
        if (!(self::$_instance instanceof Redis)) {
            self::initConfig();
            self::$_instance = new \Redis();
            //取配置
            self::$_instance->connect(self::$_config['hostname'], self::$_config['port']);
            if (self::$_config['password']) {
                self::$_instance->auth(self::$_config['password']);
            }

            if ($db > 0) {
                self::$_instance->select($db);
            } else {
                self::$_instance->select(0);
            }

            return self::$_instance;
        } else {
            return self::$_instance;
        }


    }

}
