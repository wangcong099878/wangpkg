<?php

/**
 * Created by PhpStorm.
 * User:  wangcong
 * Date: 2018/4/16
 * Time: 上午11:38
 */

namespace Wang\Pkg\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Configure;
use App\Models\Version;
use Wang\Pkg\Lib\Response;
use Wang\Pkg\Services\SmsServices;

class PublicController extends Controller
{
    //获取最新app版本
    public function version()
    {
        $version = Version::orderBy('id', 'desc')->first();
        /*        $data = [];
                $data["Code"] = 0; //0代表请求成功，非0代表失败
                $data["Msg"] = ""; //请求出错的信息
                $data["UpdateStatus"] = (int)$version->force; //0代表不更新，1代表有版本更新，不需要强制升级，2代表有版本更新，需要强制升级
                $data["VersionCode"] = (int)$version->version_number;
                $data["VersionName"] = $version->version_name;
                $data["ModifyContent"] = $version->update_content;
                $data["DownloadUrl"] = $version->apk_url;
                $data["ApkSize"] = (int)$version->apk_size;
                $data["ApkMd5"] = $version->apk_md5;  //md5值没有的话，就无法保证apk是否完整，每次都会重新下载。*/
        //Response::halt($version);
        Response::halt($version);
    }

    //获取短信验证码
    public function sendSms()
    {
        //SmsServices::check($phone, $code);
        $phone = params('phone');
        //env("SIGN_NAME")  env("TEMPLATE_CODE") env('REGION_ID','cn-hangzhou') env('ALiYUN_ACCESS_KEY_ID') env('ACCESS_KEY_SECRET')
        $result = SmsServices::send($phone);
        Response::halt($result);
    }


    //获取全部配置文件
    public function getConfig()
    {
        $type = request('type','');

        $config = Configure::where('state', 1)->get();
        if($type==1){
            $result = \Illuminate\Support\Arr::pluck($config, 'value', 'key');
            Response::halt($result);
        }

        Response::halt($config);
    }

}
