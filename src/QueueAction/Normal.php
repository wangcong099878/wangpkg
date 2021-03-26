<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2021/3/26
 * Time: 10:29 上午
 */

namespace App\QueueAction;


class Normal
{
    public static function run($q)
    {
        try {
            print_r($q);
        } catch (\Exception $e) {
            return 'Exception' . $e->getLine() . ':' . $e->getMessage();
        } catch (Error $e) {
            return 'Error' . $e->getLine() . ':' . $e->getMessage();
        } finally {
            //finally是在捕获到任何类型的异常后都会运行的一段代码,结束之前也一定会执行
            //echo "run 方法执行失败";
        }

        return "success";
    }
}
