<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2021/3/26
 * Time: 10:26 上午
 */
namespace Wang\Pkg\Lib;

class Util
{
    /**
     * 下划线转驼峰
     */
    //ucfirst(Wang\Pkg\Lib\Util::camelize('add_log'));
    public static function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }
}
