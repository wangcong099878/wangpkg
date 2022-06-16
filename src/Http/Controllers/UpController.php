<?php

/**
 * Created by PhpStorm.
 * User:  wangcong
 * Date: 2018/4/16
 * Time: 上午11:38
 */

namespace Wang\Pkg\Http\Controllers;

use App\Http\Controllers\Controller;
use Wang\Pkg\Lib\EasyRedis;
use Wang\Pkg\Lib\Response;

require app_path() . '/../vendor/qiniu/php-sdk/autoload.php';

use \Qiniu\Auth as qiniu;
use \Qiniu\Storage\UploadManager;
use SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
use \OSS\OssClient;
use \OSS\Core\OssException;
use App\Services\Auth;


class UpController extends Controller
{

    public function qiniuHtml(){
        if (env('APP_ENV') == 'local') {
            return view('wangpkg::qiniuUp');
        }
    }

    public function aliyunUp()
    {
        if (isset($_FILES['file']['tmp_name'])) {
            $user = Auth::getUserTab();
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            $key = uniqid() . '.' . $ext;

            $accessKeyId = env('ALiYUN_ACCESS_KEY_ID');
            $accessKeySecret = env('ACCESS_KEY_SECRET');

            $imgUrl = env('QINIU_IMG_URL', '');

            $endpoint = "oss-cn-beijing.aliyuncs.com";
            $bucket = "wbwan";
            try {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                $filePath = "/Users/wangcong/php/juejin/wbwan_backend/public/imgs/home.jpg";
                $ossClient->uploadFile($bucket, $key, $_FILES['file']['tmp_name']);

                //https://wbwan.oss-cn-beijing.aliyuncs.com/test.png
                Response::halt([
                    'domain' => '//wbwan.oss-cn-beijing.aliyuncs.com/',
                    'key' => $key,
                    'filepath' => 'https://wbwan.oss-cn-beijing.aliyuncs.com/' . $key
                ], 200);

            } catch (OssException $e) {
                Response::halt([], 10001, $e->getMessage());
            }

            Response::halt([
                'baseUrl' => $imgUrl,
                'filename' => $key,
                'filepath' => $imgUrl . $key,
            ],0,"上传图片成功");
        } else {
            Response::halt([],400,'上传格式不正确');
        }


    }

    public function upimg()
    {
        if (isset($_FILES['file']['tmp_name'])) {
            $user = Auth::getUserTab();
            // 用于签名的公钥和私钥
            $bucket = env('QINIU_BUCKET', '');
            $accessKey = env('QINIU_ACCESS_KEY', '');
            $secretKey = env('QINIU_SECRET_KEY', '');
            $imgUrl = env('QINIU_IMG_URL', '');
            // 初始化签权对象
            $auth = new qiniu($accessKey, $secretKey);

            // 生成上传Token
            $token = $auth->uploadToken($bucket);
            // 构建 UploadManager 对象
            $uploadMgr = new UploadManager();

            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            $key = uniqid() . '.' . $ext;

            $uploadMgr->putFile($token, $key, $_FILES['file']['tmp_name']);

            Response::halt([
                'baseUrl' => $imgUrl,
                'filename' => $key,
                'filepath' => $imgUrl . $key,
            ],0,"上传图片成功");
        } else {
            Response::halt([],400,'上传格式不正确');
        }

    }

    //获取用户权限上传
    public function upfile()
    {
        if (isset($_FILES['file']['tmp_name'])) {
            $user = Auth::getUserTab();
            // 用于签名的公钥和私钥
            $bucket = env('QINIU_BUCKET', '');
            $accessKey = env('QINIU_ACCESS_KEY', '');
            $secretKey = env('QINIU_SECRET_KEY', '');
            $imgUrl = env('QINIU_IMG_URL', '');
            // 初始化签权对象
            $auth = new qiniu($accessKey, $secretKey);

            // 生成上传Token
            $token = $auth->uploadToken($bucket);
            // 构建 UploadManager 对象
            $uploadMgr = new UploadManager();

            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            $key = uniqid() . '.' . $ext;

            $uploadMgr->putFile($token, $key, $_FILES['file']['tmp_name']);

            $user->avatar = $key;
            $user->save();

            Response::halt([
                'baseUrl' => $imgUrl,
                'filename' => $key,
                'filepath' => $imgUrl . $key . '?imageView2/0/w/200',
            ], 0, "上传图片成功");
        } else {
            Response::halt([],400,'上传格式不正确');
        }
    }

    //生成二维码 需要安装  BaconQrCodeGenerator  "simplesoftwareio/simple-qrcode": "1.3.3"
    public function createQrcode()
    {
        $url = request("url");
        $rd = EasyRedis::getInstance();
        $cachekey = "_url" . $url;
        if ($imgurl = $rd->get($cachekey)) {
            $data = [
                'url' => $imgurl
            ];
            Response::halt($data);
        }

        $qrcode = new BaconQrCodeGenerator;

        $name = uniqid() . '.png';
        $path = public_path("storage/images/{$name}");
        $imgs = $qrcode->format('png')->margin(1)->errorCorrection('H')->size(400)->merge('/public/imgs/logo.png', .15)->generate($url, $path);

        // 初始化签权对象
        $bucket = env('QINIU_BUCKET', '');
        $accessKey = env('QINIU_ACCESS_KEY', '');
        $secretKey = env('QINIU_SECRET_KEY', '');
        $imgUrl = env('QINIU_IMG_URL', '');
        // 初始化签权对象
        $auth = new qiniu($accessKey, $secretKey);
        // 生成上传Token
        $token = $auth->uploadToken($bucket);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();

        $key = $name;

        $uploadMgr->putFile($token, $key, $path);

        $filepath = $imgUrl . $key;

        $rd->set($cachekey, $filepath);
        $data = [
            'url' => $filepath
        ];
        Response::halt($data);
    }

    //获得七牛token
    public function getToken()
    {
        $bucket = env('QINIU_BUCKET', '');
        $accessKey = env('QINIU_ACCESS_KEY', '');
        $secretKey = env('QINIU_SECRET_KEY', '');
        $qiniuImgUrl = env('QINIU_IMG_URL', '');
        $auth = new qiniu($accessKey, $secretKey);
        $upToken = $auth->uploadToken($bucket);
        $ret = array('uptoken' => $upToken,'QINIU_IMG_URL'=>$qiniuImgUrl);
        echo json_encode($ret);
    }

    //获得七牛token
    public function getTokenNative()
    {
        $bucket = env('QINIU_BUCKET', '');
        $accessKey = env('QINIU_ACCESS_KEY', '');
        $secretKey = env('QINIU_SECRET_KEY', '');
        $auth = new qiniu($accessKey, $secretKey);
        $upToken = $auth->uploadToken($bucket);
        $ret = array('uptoken' => $upToken);
        Response::halt($ret);
    }

}
