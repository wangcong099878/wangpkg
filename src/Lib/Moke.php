<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2019/05/13
 * Time: 11:38
 */

namespace Wang\Pkg\Lib;

use Faker\Factory;
use Illuminate\Support\Facades\Cache;

//composer require fzaninotto/faker
class Moke
{

    //根据关联id生成数据

    //传入数组排序  传入map
    public static function arrOrderByManyFieldMap($arr, $map)
    {
        if (!is_array($arr)) {
            throw new Exception("第一个参数不为数组");
        }
        $args = [];
        foreach ($map as $key => $field) {
            if ($field == 'asc') {
                $sortCode = 4;
            } else {
                $sortCode = 3;
            }
            $temp = [];
            foreach ($arr as $index => $val) {
                $temp[$index] = $val[$key];
            }
            $args[] = $temp;
            $args[] = $sortCode;
        }
        $args[] = &$arr;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    //Wang\Pkg\Lib\Moke::delData();
    //Wang\Pkg\Lib\Moke::test(1);
    public static function test($page = 1)
    {
        /*        $array1 = [
                    ['id' => 1, 'mature' => 1, 'amount' => 10, 'time' => '2020-10-04 14:52:19'],
                    ['id' => 2, 'mature' => 2, 'amount' => 20, 'time' => '2020-11-04 14:52:19'],
                    ['id' => 3, 'mature' => 1, 'amount' => 30, 'time' => '2020-12-04 14:52:19'],
                    ['id' => 4, 'mature' => 2, 'amount' => 40, 'time' => '2020-13-04 14:52:19'],
                    ['id' => 5, 'mature' => 2, 'amount' => 40, 'time' => '2020-14-04 14:52:19'],
                    ['id' => 5, 'mature' => 2, 'amount' => 40, 'time' => '2020-14-03 14:52:19'],
                ];

                $arr = self::sortArrByManyFieldMap($array1, ['mature' => 'asc', 'amount' => 'desc', 'time' => 'asc']);
                print_r($arr);
                return;*/


        $a = 1;
        $b = 1;
        $c = "==";

        $res = '2';
        //eval("\$res = '1' {$c} '1';");
        eval("\$res = {$a} {$c} {$b};");

        var_dump($res);

        return 1;

        return self::getList([
            'map' => [
                'name' => 'name',
                'state' => function ($faker) {
                    return rand(1, 10);
                }
            ],
            'page' => $page,
            'defaultField' => true,
            'isCache' => false,
            'sortMap' => ['id' => 'desc']
        ]);

        return 1;

        //https://github.com/fzaninotto/Faker

        //$faker = Factory::create('zh_CN');
        //手机号码
        //echo $faker->phoneNumber;

        //银川沙市区
        //echo $faker->address;

        //这个只能是英文的
        //echo $faker->text;
        //echo $faker->word;
        //echo $faker->realText(200, 2);
        //echo $faker->email;
        //echo $faker->userName;
        //echo $faker->ipv4;
        //echo $faker->url;
        //深度2级，级别不超过3个子元素
        //echo $faker->randomHtml(2,3);


        //echo $faker->streetAddress;
        //echo $faker->lastName;  //姓
        //echo $faker->firstName; //名

        //var_dump($faker->boolean(25));  //25%概率为 true

        //2018-12-24
        //echo $faker->dateTimeThisCentury->format('Y-m-d');

        //城市
        //echo $faker->city;

        //邮编  国内没用
        //echo $faker->postcode;

        //省
        //echo $faker->state;

        //公司  有用
        //echo $faker->company;


        //口号   有用
        //echo $faker->catchPhrase;

        //姓名
        //echo $faker->name;

        //只支持英文
        //echo $faker->text(400);


        //randomNumber($nbDigits = NULL, $strict = false) // 79907610
        //randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL); //48.8932
        //numberBetween($min = 1000, $max = 9000) // 8567

        //
        //var_dump($faker->optional(0.1)->randomDigit);  //90% 出现NULL
        //var_dump($faker->optional(0.9)->randomDigit);  //10% 出现NULL

        //$faker->optional($weight = 0.5, $default = false)->randomDigit; // 50% 出现 FALSE

        //$faker->optional($weight = 0.9, $default = 'abc')->word; // 10% 出现 'abc'

        //姓名
        //$name="name";
        //echo $faker->{$name};
        //self::create();

        /*
            $values = array();
            for ($i = 0; $i < 10; $i++) {
              // get a random digit, but always a new one, to avoid duplicates
              $values []= $faker->unique()->randomDigit;
            }
            print_r($values); // [4, 1, 8, 5, 0, 2, 6, 9, 7, 3]
         */


    }

    //
    public static function createObj()
    {

    }

    public static function netCreateList()
    {
        //$page = request('page');
        //$page_size = request('page_size');
    }

    /*
     * 根据多个字段查询二维数组的数据
     */
    public static function searchData($datas, $searchMap = [], $searchMode = '&&')
    {
        /*        $searchMap = [
                    ['orderPhone', '=', ''],
                    ['mediaId', '=', ''],
                    ['mediaId', '!=', ''],
                    ['mediaId', '>=', ''],
                    ['mediaId', '<=', ''],
                    ['created_at', 'between', ['', '']],          //范围
                    ['name', 'like', ''],          //模糊查询
                ];*/

        $resultData = [];

        $countSign = count($searchMap);

        foreach ($datas as $data) {
            //score可以返回做为权重使用
            $score = self::searchObj($data, $searchMap, $searchMode);

            if ($searchMode == '&&') {
                if ($score == $countSign) {
                    array_push($resultData, $data);
                }
            } else {
                if ($score > 0) {
                    array_push($resultData, $data);
                }
            }
        }

        return $resultData;

    }


    public static function searchObj($data, $searchMap, $searchMode)
    {

        $sign = 0;

        foreach ($searchMap as $key => $val) {

            //try {
            $field = $val[0];
            $condition = $val[1];
            $searchData = $val[2];
            $fieldData = $data[$field];

            if ($condition == 'between') {
                if ($fieldData >= $val[2] && $fieldData <= $val[3]) {
                    $sign++;
                    if ($searchMode == '||') {
                        break;
                    }
                }
            } elseif ($condition == 'like') {
                if (mb_stripos($fieldData, $searchData) !== false) {
                    $sign++;
                    if ($searchMode == '||') {
                        break;
                    }
                }
            } elseif ($condition == 'in') {
                //包含在多个值里面
                if (in_array($fieldData, $searchData)) {
                    $sign++;
                    if ($searchMode == '||') {
                        break;
                    }
                }

            } elseif ($condition == 'likeIn') {
                //模糊包含在多个值里面
                foreach ($searchData as $likeKey) {
                    if (mb_stripos($fieldData, $likeKey) !== false) {
                        $sign++;
                        if ($searchMode == '||') {
                            break;
                        }
                    }
                }

            } else {
                /*                            $res = '';
                                            eval("\$res = {$fieldData} {$condition} {$searchKey};");

                                            if ($res) {
                                                array_push($resultData, $v);
                                                break;
                                            }*/

                if (is_string($fieldData)) {
                    if (eval("return '{$fieldData}' {$condition} '{$searchData}';")) {
                        $sign++;
                        if ($searchMode == '||') {
                            break;
                        }
                    }
                } else {
                    if (eval("return {$fieldData} {$condition} {$searchData};")) {
                        $sign++;
                        if ($searchMode == '||') {
                            break;
                        }
                    }
                }

            }


            /*                } catch (\Exception $e) {

                            }*/
        }

        return $sign;


    }

    //获取一个字段的数组  用于创建关联数据
    public static function getFieldArr()
    {

    }


    public static function delData($apiName = 'default')
    {
        $md5Name = md5($apiName);
        return Cache::store('file')->delete($md5Name);
    }

    //初始化数据
    public static function initData($apiName = 'default', $map = ['id' => 'int'], $dataNum = 200, $isCache = true, $startDate = '', $endDate = '')
    {
        try {
            $md5Name = md5($apiName);

            $datas = Cache::store('file')->get($md5Name);

            if (!$datas || $isCache == false) {

                $faker = Factory::create('zh_CN');
                $datas = [];
                for ($i = 1; $i <= $dataNum; $i++) {
                    $data = [];

                    foreach ($map as $k => $v) {
                        if ($v == 'id') {
                            $data[$v] = $i;
                            continue;
                        }

                        if ($v == 'created_at' || $v == 'updated_at') {
                            $data[$v] = self::randTime($endDate, $startDate);
                            continue;
                        }

                        if ($v == 'rand_time') {
                            $data[$v] = self::randTime($endDate, $startDate);
                            continue;
                        }

                        if ($v == 'amount') {
                            $data[$v] = self::randAmount();
                            continue;
                        }


                        //判断是否是匿名函数
                        if (is_callable($v)) {
                            try {
                                $data[$k] = $v($faker);
                            } catch (\Exception $e) {
                                $data[$k] = '';
                            }
                        } else {
                            //判断是否是字符串
                            try {
                                $data[$k] = $faker->{$v};
                            } catch (\Exception $e) {
                                $data[$k] = '';
                            }
                        }
                        //判断数组
                        try {
                            if (is_array($v)) {
                                $num = rand(0, count($v)-1);
                                $data[$k] = $v[$num];
                            }
                        } catch (\Exception $e) {
                            $data[$k] = '';
                        }
                    }

/*                        */

                        array_push($datas, $data);
                    }

                    if ($isCache) {
                        Cache::store('file')->add($md5Name, $datas, 60 * 60 * 24);
                    }

                }
            else {
                    //echo "有缓存";
                }

                return $datas;
            }
        catch
            (\Exception $e) {
                //errLog('liantong', "登录错误", $e);
            }

    }

    //随机金额  随机浮点数 \Wang\Pkg\Lib\Moke::randAmount();
    public static function randAmount($maxNum = 1000, $scale = 2)
    {

        $seed = bcadd(rand(0, $maxNum * 100), rand(0, 100));

        return bcdiv($seed, '100', $scale);
        //bcadd()
    }

    //随机获取时间戳 默认是最近三个月
    public static function randTime($endDate = '', $startDate = '')
    {
        $endTime = time();
        $startTime = strtotime('-3 month');
        if ($endDate != '') {
            $endTime = strtotime($endDate);
        }
        if ($startDate != '') {
            $startTime = strtotime($startDate);
        }

        return date('Y-m-d H:i:s', rand($startTime, $endTime));
    }

    //https://220.m.molibx.com/api/sign_login
    //strtoupper(md5(app_id=101&timestamp=1597137141&user_id=1fsx77w0mcom9G9iDFyHvYm6hMDAyVE9F))
    public static function getList($config = [])
    {
        $map = [];
        $apiName = 'default';
        $defaultField = true;
        $isCache = true;
        $page = 1;
        $pageSize = 10;
        $maxPage = 100;
        $sortMap = [];
        $searchMode = '&&';
        $searchMap = [];

        //时间范围
        $endTime = time();
        $startTime = strtotime('-3 month');

        $startDate = date('Y-m-d H:i:s', $startTime);
        $endDate = date('Y-m-d H:i:s', $endTime);

        /*        $defaultConfig = [
                    'map' => [],
                    'apiName' => 'default',
                    'isDefault' => true,//是否默认添加   'id', 'created_at', 'updated_at',
                    'isCache' => true,
                    'page'=>1,
                    'pageSize'=>20,
                    'maxPage'=>1000,
                    'orderBy'=>'desc',
                ];
                $config = array_replace($defaultConfig, $customConfig);
        */
        extract($config);

        if ($defaultField) {
            $map = array_merge(['id'], $map);
            $map = array_merge($map, ['created_at', 'updated_at']);
        }

        //是否缓存 is_cache
        $dataNum = $maxPage * $pageSize;

        //生成数据
        $datas = self::initData($apiName, $map, $dataNum, $isCache, $startDate, $endDate);

        /*        $searchMode = '&&';

                $searchMap = [
                    '&&',
                    ['orderPhone', '=', ''],
                    ['mediaId', '=', ''],
                    ['mediaId', '!=', ''],
                    ['mediaId', '>=', ''],
                    ['mediaId', '<=', ''],
                    ['created_at', 'between', ['', '']],          //范围
                    ['name', 'like', ''],          //模糊查询
                ];*/

        //处理查询   等于查询   大小查询   范围查询   模糊查询
        // !!! 二维数据搜索 !!!
        /*        if (!empty($search)) {
                    foreach ($tasklog as $sk => $sv) {
                        if (mb_stripos($sv['tasklog_name'], $search) === false
                            && mb_stripos($sv['tasklog_type'], $search) === false
                            && mb_stripos($sv['tasklog_user'], $search) === false
                        ) {
                            unset($tasklog[$sk]);
                            continue;
                        }
                    }
                }*/


        if ($searchMap) {
            $datas = self::searchData($datas, $searchMap, $searchMode);
        }

        //处理排序
        if ($sortMap) {
            //print_r($sortMap);
            $datas = self::arrOrderByManyFieldMap($datas, $sortMap);

            //print_r($datas);
        }


        $count = count($datas);//总条数
        $start = ($page - 1) * $pageSize;//偏移量，当前页-1乘以每页显示条数
        $result = array_slice($datas, $start, $pageSize);

        return [
            'total' => $count,
            'data' => $result
        ];
    }
}
