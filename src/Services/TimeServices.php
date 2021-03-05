<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2019/04/24
 * Time: 15:20
 */

namespace Wang\Pkg\Services;


class TimeServices
{
    //App\Services\TimeServices::range("CurrentMonth");
    public static function range($type = "")
    {
        $startTime = 0;
        $endTime = 0;

        if ($type == 'LastMonth') {
            $startTime = date('Y-m-01 00:00:00', strtotime('-1 month'));
            $endTime = date('Y-m-t 23:59:59', strtotime('-1 month'));
        }

        if ($type == 'CurrentMonth') {
            $startTime = date('Y-m-01 00:00:00');
            $endTime = date('Y-m-d 23:59:59');
        }

        if ($type == 'Today') {
            $startTime = date('Y-m-d 00:00:00');
            $endTime = date('Y-m-d 23:59:59');
        }

        if ($type == 'Yesterday') {
            $startTime = date('Y-m-d 00:00:00', strtotime('-1 day'));
            $endTime = date('Y-m-d 23:59:59', strtotime('-1 day'));
        }

        if ($type == 'Threedays') {
            $startTime = date('Y-m-d 00:00:00', strtotime('-3 day'));
            $endTime = date('Y-m-d 23:59:59');
        }

        return [
            $startTime, $endTime
        ];
    }

    public static function getDate()
    {
        echo "今天:" . date("Y-m-d") . "<br>";
        echo "昨天:" . date("Y-m-d", strtotime("-1 day")), "<br>";
        echo "明天:" . date("Y-m-d", strtotime("+1 day")) . "<br>";
        echo "一周后:" . date("Y-m-d", strtotime("+1 week")) . "<br>";
        echo "一周零两天四小时两秒后:" . date("Y-m-d G:H:s", strtotime("+1 week 2 days 4 hours 2 seconds")) . "<br>";
        echo "下个星期四:" . date("Y-m-d", strtotime("next Thursday")) . "<br>";
        echo "上个周一:" . date("Y-m-d", strtotime("last Monday")) . "<br>";
        echo "一个月前:" . date("Y-m-d", strtotime("last month")) . "<br>";
        echo "一个月后:" . date("Y-m-d", strtotime("+1 month")) . "<br>";
        echo "十年后:" . date("Y-m-d", strtotime("+10 year")) . "<br>";
    }
}
