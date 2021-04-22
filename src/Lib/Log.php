<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2019/05/13
 * Time: 11:38
 */

namespace Wang\Pkg\Lib;

use Throwable;

class Log
{
    //关闭消息日志
    public static function setMsgLog($val = 1)
    {
        putenv('off_msg_log=' . $val);
        return true;
    }

    //关闭错误日志
    public static function setErrorLog($val = 1)
    {
        putenv('off_error_log=' . $val);
        return true;
    }

    //普通消息格式
    public static function msgLog($type, $msg,$debugInfo)
    {
        if (getenv('off_msg_log') !== "1") {
            //类型  时间  文件  行号   消息
            return $type . '|' . date('Y-m-d H:i:s') . '|' . $debugInfo[0]['file'] . '|' . $debugInfo[0]['line'] . '|' . $msg . PHP_EOL;
        }
        return '';
    }

    //Wang\Pkg\Lib\Log::showMsgLog();
    public static function showMsgLog($msg,$type='fatal_err')
    {
        $debugInfo = debug_backtrace();
        $str = static::msgLog($type, $msg,$debugInfo);
        if ($str) {
            print_r($str);
        }
    }

    //错误消息格式
    //Wang\Pkg\Lib\Log::showErrLog();
    public static function errLog(Throwable $e)
    {
        if (getenv('off_error_log') !== "1") {
            //类型 时间 文件 行  消息内容
            return 'err' . '|' . date('Y-m-d H:i:s') . '|' . $e->getFile() . '|' . $e->getLine() . '|' . $e->getMessage() . PHP_EOL;
        }
        return '';
    }

    //Wang\Pkg\Lib\Log::showErrLog();
    public static function showErrLog(Throwable $e)
    {
        $str = static::errLog($e);
        if ($str) {
            print_r($str);
        }

    }
}
