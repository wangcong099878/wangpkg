<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2021/3/26
 * Time: 10:29 上午
 */

namespace App\QueueAction;
use Wang\Pkg\Lib\Log;

class Normal
{
    //单独调试 App\QueueAction\Normal::run(\App\Models\Queue::find(1)->toArray());
    public static function run($q)
    {
        try {
            print_r($q);
        } catch (\Throwable $e) {

            Log::showMsgLog('执行队列发生异常','run');
            Log::showErrLog($e);

            return 'Exception' . $e->getLine() . ':' . $e->getMessage();
        } finally {
            //finally是在捕获到任何类型的异常后都会运行的一段代码,结束之前也一定会执行
            //echo "run 方法执行失败";
        }

        return "success";
    }
}
