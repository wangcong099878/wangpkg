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

        if ($type == 'Week') {
            $startTime = date('Y-m-d 00:00:00', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
            $endTime = date('Y-m-d 23:59:59', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));
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

        //本周一,w为星期几的数字形式,这里0为周日
        echo date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)) . "\r\n";


//本周日,同样使用w,以现在与周日相关天数算
        echo date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600)) . "\r\n";

//本周一
        $beginWeek = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
//本周日
        $endWeek = mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));


//上周一,无论今天几号,-1 monday为上一个有效周未,-1不行只能-2
        echo date('Y-m-d', strtotime('-2 monday', time())) . "\r\n";


//上周日,上一个有效周日,同样适用于其它星期
        echo date('Y-m-d', strtotime('-1 sunday', time())) . "\r\n";


//本月一号
        echo date('Y-m-d', strtotime(date('Y-m', time()) . '-01 00:00:00')) . "\r\n";


//本月最后一天
        echo date('Y-m-d', strtotime(date('Y-m', time()) . '-' . date('t', time()) . ' 00:00:00')) . "\r\n"; //t为当月天数,28至31天

        //本月一号
        $beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
//本月最后一天
        $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));

//上月一号
        echo strtotime(date('Ym01', strtotime('-1 month'))) . "\r\n";


//上月最后一天
        echo strtotime(date('Ymd 23:59:59', strtotime(date('Y-m-01') . ' -1 day'))) . "\r\n"; //本月一日减一天即是上月最后一日

        $season = ceil(date('n') / 3); //获取月份的季度
// 本季度
        dump(date('Y-m-01', mktime(0, 0, 0, ($season - 1) * 3 + 1, 1, date('Y'))));
        dump(date('Y-m-t', mktime(0, 0, 0, $season * 3, 1, date('Y'))));

//上季度
        dump(date('Y-m-01', mktime(0, 0, 0, ($season - 2) * 3 + 1, 1, date('Y'))));
        dump(date('Y-m-t', mktime(0, 0, 0, ($season - 1) * 3, 1, date('Y'))));

//上一年
        echo date('Y-01-01', strtotime('-1 year'));
        echo date('Y-12-31', strtotime('-1 year'));

// PHP 获取某月份的第一天和最后一天
        $month = "2022-05";
        $sDate = date("Y-m-d", strtotime(date("Y-m", strtotime($month))));
        $eDate = date("Y-m-d", strtotime(date("Y-m", strtotime($month)) . "+1month-1day"));
        var_dump($sDate, $eDate); //2022-05-01, 2022-05-31

    }
}
