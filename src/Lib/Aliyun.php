<?php

/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2019/05/13
 * Time: 11:38
 */

namespace Wang\Pkg\Lib;


class Aliyun
{

    //composer require aliyuncs/oss-sdk-php
    //$imgBase64 图片base64格式
    public static  function imageDoAliyunOss($imgBase64)
    {
        #引用阿里云上传文件
        require 'AliYunUpload.php';
        #转化base64编码图片
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $imgBase64, $res)) {
            //获取图片类型
            $type = $res[2];
            //图片名字
            $fileName = md5(time()) . '.' . $type;
            // 临时文件
            $tmpfname = tempnam("/image/", "FOO");
            //保存图片
            $handle = fopen($tmpfname, "w");
            //阿里云oss上传的文件目录
            $dst = 'zxnew/';
            if (fwrite($handle, base64_decode(str_replace($res[1], '', $imgBase64)))) {
                #上传图片至阿里云OSS
                $aliyun = new AliYunUpload();
                $url = $aliyun->uploadImage($dst . $fileName, $tmpfname);
                #关闭缓存
                fclose($handle);
                #删除本地该图片
                unlink($tmpfname);
                #返回图片链接
                $returnUrl = '文件域名' . $dst . $fileName;
                return $returnUrl;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
    //  下面我们再来封装阿里云OSS上传的方法，代码如下：
    public static  function uploadImage($dst, $getFile)
    {
        #配置OSS基本配置
        $config = array(
            'KeyId' => env('ALiYUN_ACCESS_KEY_ID'),
            'KeySecret' => env('ALiYUN_ACCESS_KEY_SECRET'),
            'Endpoint' => env('ALiYUN_ENDPOINT'),
            'Bucket' => env('ALiYUN_BUCKET'),
        );
        $ossClient = new OssClient($config['KeyId'], $config['KeySecret'],
            $config['Endpoint']);
        #执行阿里云上传
        $result = $ossClient->uploadFile($config['Bucket'], $dst, $getFile);
        #返回
        return $result;
    }

}
