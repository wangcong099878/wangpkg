<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/05/13
 * Time: 17:19
 */

namespace Wang\Pkg\Services;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Wang\Pkg\Lib\Response;
use App\Models\Codelist;
use Wang\Pkg\Lib\Request;

//composer require "alibabacloud/sdk"
class SmsServices
{

    //Wang\Pkg\Services\SmsServices::check(13917645030,888888);
    public static function check($phone, $code)
    {
        if (env('WHITE_PHONE')) {
            $whitelist = explode(',', env('WHITE_PHONE'));
            if (!in_array($phone, $whitelist)) {
                return true;
            }
        }

        $obj = Codelist::where("phone", $phone)->orderBy('updated_at', 'desc')->first();
        if (!$obj || $code != $obj->code) {
            Response::halt([], 201, "验证码错误");
        }

        $codetime = strtotime($obj->updated_at);
        $time = time();

        $cday = $codetime + 120;
        if ($cday < $time) {
            if (!in_array(env('APP_ENV', ''), ['dev', 'local'])) {
                Response::halt([], 202, "验证码已过期");
            }
        }

        return true;
    }

    public static function sendMsg($phone, $scene)
    {
        return self::send($phone, $scene, "");
    }

    //App\Services
    //  Wang\Pkg\Services\SmsServices::send(13917645030);
    public static function send($phone, $scene = 1, $tag = "")
    {
        $code = mt_rand(100000, 999999);

        if (in_array(env('APP_ENV', ''), ['dev', 'local'])) {
            $code = 888888;
        }

        $ip = Request::getClientIp();

        $count = Codelist::where('ip', $ip)->whereBetween('created_at', [date("Y-m-d H:i:s", strtotime("-12 hour")), date("Y-m-d H:i:s")])->count();

        if (!in_array(env('APP_ENV', ''), ['dev', 'local'])) {
            if ($count > 4) {
                Response::halt([], 201, "获取次数超出限制");
            }
        }

        $verifyCode = new Codelist();
        $verifyCode->phone = $phone;
        $verifyCode->scene = $scene;
        $verifyCode->ip = $ip;
        $verifyCode->code = $code;
        $verifyCode->save();

        /*        if (env('APP_ENV', '') == 'dev' || env('APP_ENV', '') == 'local') {
                    return "OK";
                }*/
        if (in_array(env('APP_ENV', ''), ['dev', 'local'])) {
            return "dev send ok!";
        }

        return self::AliSend($phone, $code, $tag);
    }

    public static function AliSend($phone, $code, $tag = "")
    {
        if ($tag == "") {
            $tag = env("SIGN_NAME");
        }

        $TemplateCode = env("TEMPLATE_CODE");

        $regionId = env('REGION_ID', 'cn-hangzhou');

        // Download：https://github.com/aliyun/openapi-sdk-php
        // Usage：https://github.com/aliyun/openapi-sdk-php/blob/master/README.md
        AlibabaCloud::accessKeyClient(env('ALiYUN_ACCESS_KEY_ID'), env('ACCESS_KEY_SECRET'))
            ->regionId($regionId)
            ->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'RegionId' => $regionId,
                        'PhoneNumbers' => $phone,
                        'SignName' => $tag,
                        //'TemplateCode' => "SMS_157449276",
                        //'TemplateCode' => "SMS_210580051",
                        'TemplateCode' => $TemplateCode,
                        'TemplateParam' => "{code:{$code}}",
                        /*                        'SmsUpExtendCode' => "3345",
                                                'OutId' => "3345",*/
                    ],
                ])->request();
            $result = $result->toArray();

            if ($result['Code'] == 'OK') {
                //其他错误
                return 'OK';
            } else {
                return $result['Message'];
            }
        } catch (ClientException $e) {
            //连接错误
            return $e->getErrorMessage();
        } catch (ServerException $e) {
            //服务器端错误
            return $e->getErrorMessage();
        }

    }

    /**
     * 发送短信
     */
    function sendSms($phone, $code)
    {

        $params = array();

        // *** 需用户填写部分 ***
        // fixme 必填：是否启用https
        $security = false;

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "";
        $accessKeySecret = "";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $phone;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        //$params["SignName"] = "";
        $params["SignName"] = "";

        //
        //SMS_201721640
        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = array(
            "code" => $code,
            //"product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        //$params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            )),
            $security
        );

        return $content;
    }


}
