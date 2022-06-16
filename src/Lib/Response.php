<?php

/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2018/4/16
 * Time: 上午11:38
 */

namespace Wang\Pkg\Lib;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;

class Response
{

    protected static $_instance;

    public static $map = [
        0 => '请求成功',
        201 => '暂无数据',
        202 => '已接受',    //异步处理
        400 => '请求参数错误',
        401 => '请求要求用户的身份认证',
        403 => '服务器理解请求客户端的请求，但是拒绝执行此请求',
        404 => '请求失败',
        405 => '请求行中指定的请求方法不能被用于请求相应的资源',
        410 => '被请求的资源在服务器上已经不再可用',
        510 => '获取资源所需要的策略并没有没满足',

        #系统与用户相关
        1000 => "系统内部错误",
        1001 => "验证码错误",
        1002 => "两次密码不一致",
        1003 => "该手机号已注册",
        1004 => "密码错误",
        1005 => "未登录请退出重新登录",
        1006 => "账号或密码错误",
        1007 => "该账号已经注册",
        1008 => "账户异常",
        1009 => "改昵称已被使用",
        1010 => "验证码过期",
        1011 => "该手机号未注册",
        1012 => "未同意用户协议",
        1013 => "用户信息不完善",
        #接口相关
        2001 => "缺少参数",
        2002 => "参数不正确",
        2003 => "验签失败",
        #任务相关
        3001 => "任务不存在",
        3002 => "已经提交过该任务",
        3003 => "该游戏已下线",
        3004 => '同一个任务已经有一个正在进行中',
        3005 => '任务表单提交错误',
        3006 => '后台审核相关错误',
        3007 => '该任务加量支付金额不足',
        3008 => '任务已被强制暂停',
        #登录相关
        5001 => "用户不存在",
        5002 => "该手机或者qq已被注册",
        5003 => "网络错误",  //该账号已被冻结
        5004 => "该qq已被使用",
        #权限相关
        4001 => "未登录请退出重新登录",
        4002 => "无权访问！",
        #提现相关
        6001 => "金额不足",
        6002 => "请输入正确的金额",
        #其他错误
        7001 => "请求发生错误",
        7002 => "非法请求",
        #刷新卡
        8001 => "刷新卡量不足",
        #
        9001 => "该条不存在",
        9002 => "未审核通过",
        9003 => "该奖励已领取",
        9004 => "无权审核",
        9005 => "数据已存在",


    ];

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    //自定义错误消息
    public static function cErr($msg)
    {
        self::halt([], 201, $msg);
    }

    //
    public static function success($errorCode = 0, $message = "请求成功")
    {
        $data = [];
        $options = [];
        \header('Content-Type: application/json; charset=utf-8');
        exit(self::res($data, $errorCode, $message, $options));
    }

    public static function err()
    {
        self::halt([], 201, "发生错误");
    }

    //暂无数据
    //10001数字  10002文本   10003对象   10004数组
    public static function null($errorCode = 10004, $message = "暂无数据")
    {
        $data = self::codeState($errorCode);
        $options = [];
        \header('Content-Type: application/json; charset=utf-8');
        exit(self::res($data, $errorCode, $message, $options));
    }

    public static function codeState($code){
        $data = "";
        switch ($code) {
            case 10001:
                $data = 0;
                break;
            case 10002:
                $data = "";
                break;
            case 10003:
                $data = new \stdClass();
                break;
            case 10004:
                $data = [];
                break;
            default:

        }

        return $data;
    }

    public static function illegal()
    {
        self::halt([], 7002);
    }

    public static function returnAjaxPage($object, $field = null, $count = 0)
    {
        $currentPage = request('currentpage', 1);
        $num = request('num', 50);
        $start = ($currentPage - 1) * $num;

        if ($count == 1) {
            $countNum = $object->count();
            self::halt($object->offset($start)->limit($num)->get($field), 200, "请求成功", [
                'count' => $countNum
            ]);
        }
        self::isNoData($object->offset($start)->limit($num)->get($field));
    }

    public static function isNoData($data = [], $code = 200, $message = "", $options = [])
    {
        if ($data) {
            self::halt($data);
        } else {
            self::halt($data, 201, "暂无更多数据了");
        }
    }

    public static function halt($data = [], $errorCode = 0, $message = "", array $options = [])
    {
        \header('Content-Type: application/json; charset=utf-8');

        if (isset($_SERVER['HTTP_OS']) && $_SERVER['HTTP_OS'] == 'android') {
            if (empty($data)) {
                $data = null;
            }
        }
        exit(self::res($data, $errorCode, $message, $options));
    }


    public static function nativeHalt($data = [], $code = 0, $message = "", $options = [])
    {
        \header('Content-Type: application/json; charset=utf-8');
        exit(self::res($data, $code, $message, $options));
    }


    public static function swoole($data = null, $errorCode = 200, $message = "", $options = [])
    {
        //\header('Content-Type: application/json; charset=utf-8');
        if (!$message) {
            $message = self::$map[$errorCode];
        }

        /*        if (isset($_SERVER['HTTP_OS']) && $_SERVER['HTTP_OS'] == 'android') {

                    if ($data == []) {
                        $data = null;
                    } else {
                        if (!$data) {
                            $data = null;
                        }
                    }
                }*/

        $result = [
            'err' => $errorCode,
            'message' => $message,
            'data' => $data
        ];

        /*        $options['new_token'] = '';
                if (isset($_REQUEST['new_token'])) {
                    $options['new_token'] = $_REQUEST['new_token'];
                }*/

        $result = array_merge($options, $result);

        if (env('PHP_HTTP_LOG_IS_OPEN')) {
            //NLog::httpLogSaveToRedis($result, '', 'response');
        }

        return $result;
        //$resJson = json_encode($result, JSON_UNESCAPED_UNICODE);
        //将返回信息记录到redis队列

        //return $resJson;
    }


    public static function res($data = null, $errorCode = 200, $message = "", $options = [])
    {
        \header('Content-Type: application/json; charset=utf-8');
        if (!$message) {
            $message = self::$map[$errorCode];
        }

        /*        if (isset($_SERVER['HTTP_OS']) && $_SERVER['HTTP_OS'] == 'android') {

                    if ($data == []) {
                        $data = null;
                    } else {
                        if (!$data) {
                            $data = null;
                        }
                    }
                }*/

        $result = [
            'err' => $errorCode,
            'message' => $message,
            'data' => $data
        ];

/*        $options['new_token'] = '';
        if (isset($_REQUEST['new_token'])) {
            $options['new_token'] = $_REQUEST['new_token'];
        }*/

        $result = array_merge($options, $result);

        if (env('PHP_HTTP_LOG_IS_OPEN')) {
            //NLog::httpLogSaveToRedis($result, '', 'response');
        }


        $resJson = json_encode($result, JSON_UNESCAPED_UNICODE);
        //将返回信息记录到redis队列

        return $resJson;
    }

    public static function json($result = array(), $errorCode = 0, $message = null, $options = array())
    {
        $result = array('err' => $errorCode, 'message' => $message, 'data' => $result);
        if (is_array($options)) {
            $result = array_merge($options, $result);
        }
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }


    public static function rest($status = 'ok', $code = 200, $payload = [], $message = '', $options = [])
    {

        if (!$message) {
            $message = self::$map[$code];
        }

        if (is_array($options)) {
            $payload = array_merge($payload, $options);
        }

        http_response_code($code);

        $result = array('status' => $status, 'code' => $code, 'message' => $message, 'payload' => $payload);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public static function getAllHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }


}
