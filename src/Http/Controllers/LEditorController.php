<?php

/**
 * Created by PhpStorm.
 * User:  wangcong
 * Date: 2018/4/16
 * Time: 上午11:38
 */

namespace Wang\Pkg\Http\Controllers;

use App\Http\Controllers\Controller;

//composer require "qiniu/php-sdk"
require base_path() . '/vendor/qiniu/php-sdk/autoload.php';

use \Qiniu\Auth as qiniu;
use \Qiniu\Storage\UploadManager;
use Wang\Pkg\Lib\Ueditor\Uploader;

/*图片将上传到本地public/storage目录下*/

class LEditorController extends Controller
{

    public function fileUpload()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Allow-Headers: Authorization,Origin, X-Requested-With, Content-Type, Accept');
        header('Access-Control-Allow-Methods: GET,POST');


        try {
            //Post变量由2M修改为8M，此值改为比upload_max_filesize要大
            ini_set('post_max_size', '120M');
            //上传文件修改也为8M，和上面这个有点关系，大小不等的关系。
            ini_set('upload_max_filesize', '100M');
            //正在运行的脚本大量使用系统可用内存,上传图片给多点，最好比post_max_size大1.5倍
            ini_set('memory_limit', '200M');

            // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
            // echo "Type: " . $_FILES["file"]["type"] . "<br />";
            // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
            // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

            $CONFIG = config('wangpkg.ueditor_config');

            $dirname = public_path('storage');

            //图片地址前缀
            if(!file_exists($dirname)){
                !mkdir($dirname, 0777, true);
            }

            $ext = substr(strrchr($_FILES["upload"]["name"], '.'), 1);
            $filename = uniqid('file_').'.'.$ext;
            $filepath = $dirname.'/'.$filename;


            move_uploaded_file($_FILES["upload"]["tmp_name"], $filepath);
            //

            return ['status' => 1, 'url' => env('LOCAL_STATIC_URL').'/storage/'.$filename, 'files' => $_FILES];


        } catch (Exception $e) {
            return ['status' => 0, 'url' => env('QINIU_IMG_URL'), 'files' => $_FILES, 'msg' => $e->getMessage()];
        }
    }

    public function wangUpload()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Allow-Headers: Authorization,Origin, X-Requested-With, Content-Type, Accept');
        header('Access-Control-Allow-Methods: GET,POST');


        if (request('updateType') != 'admin') {
            /*            $user = UserAuthServices::fastGetUser();
                        if (!$user) {
                            return ['status' => 0, 'url' => '', 'msg' => '请登录！', 'files' => $user];
                        }
                        $uid = $user->id;*/
        }

        try {
            //Post变量由2M修改为8M，此值改为比upload_max_filesize要大
            ini_set('post_max_size', '120M');
            //上传文件修改也为8M，和上面这个有点关系，大小不等的关系。
            ini_set('upload_max_filesize', '100M');
            //正在运行的脚本大量使用系统可用内存,上传图片给多点，最好比post_max_size大1.5倍
            ini_set('memory_limit', '200M');


            if ((($_FILES["upload"]["type"] == "image/png")
                    || ($_FILES["upload"]["type"] == "image/jpeg")
                    || ($_FILES["upload"]["type"] == "image/pjpeg")
                    || ($_FILES["upload"]["type"] == "image/vnd.microsoft.icon")
                )
                && ($_FILES["upload"]["size"] < 2000000000)) {
                if ($_FILES["upload"]["error"] > 0) {
                    return ['uploaded' => false, 'url' => ''];
                } else {

                    // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
                    // echo "Type: " . $_FILES["file"]["type"] . "<br />";
                    // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
                    // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

                    $CONFIG = config('wangpkg.ueditor_config');

                    //图片地址前缀
                    $CONFIG['imageUrlPrefix'] = '/';
                    $config = array(
                        "pathFormat" => $CONFIG['imagePathFormat'],
                        "maxSize" => $CONFIG['imageMaxSize'],
                        "allowFiles" => $CONFIG['imageAllowFiles']
                    );
                    /* 生成上传实例对象并完成上传 */
                    $up = new Uploader('upload', $config, 'upload');

                    $fileinfo = $up->getFileInfo();

                    //$up->getFilePath()
                    /*                    $bucket = env('QINIU_BUCKET');
                                        $accessKey = env('QINIU_ACCESS_KEY');
                                        $secretKey = env('QINIU_SECRET_KEY');

                                        // 初始化签权对象
                                        $auth = new qiniu($accessKey, $secretKey);
                                        // 生成上传Token
                                        $token = $auth->uploadToken($bucket);
                                        // 构建 UploadManager 对象
                                        $uploadMgr = new UploadManager();

                                        $pos = strpos($_FILES["upload"]["name"], '.', 1);
                                        $suffix = substr($_FILES["upload"]["name"], $pos + 1);
                                        $key = uniqid() . '.' . rtrim($suffix, '.');
                                        //$key = substr($fileinfo['url'], 1);
                                        $uploadMgr->putFile($token, $key, $_FILES["upload"]["tmp_name"]);*/

                    //return ['status' => 1, 'url' => env('QINIU_IMG_URL') . $key . '?imageView2/1', 'files' => $_FILES];
                    return ['status' => 1, 'url' => env('LOCAL_IMG_URL') . $fileinfo['url'], 'files' => $fileinfo];
                }
            } else {
                return ['status' => 0, 'url' => '', 'msg' => '上传失败', 'files' => $_FILES];
            }
        } catch (Exception $e) {
            return ['status' => 0, 'url' => env('QINIU_IMG_URL'), 'files' => $_FILES, 'msg' => $e->getMessage()];
        }
    }

    public function ckUpload()
    {
        try {
            ini_set('post_max_size', '120M');
            ini_set('upload_max_filesize', '100M');
            //正在运行的脚本大量使用系统可用内存,上传图片给多点，最好比post_max_size大1.5倍
            ini_set('memory_limit', '200M');
            /*$bucket = env('QINIU_BUCKET');
            $accessKey = env('QINIU_ACCESS_KEY');
            $secretKey = env('QINIU_SECRET_KEY');
            $auth = new qiniu($accessKey, $secretKey);
            $token = $auth->uploadToken($bucket);
            $uploadMgr = new UploadManager();
            $pos = strpos($_FILES["upload"]["name"], '.', 1);
            $suffix = substr($_FILES["upload"]["name"], $pos + 1);
            $key = uniqid() . '.' . rtrim($suffix, '.');
            $uploadMgr->putFile($token, $key, $_FILES["upload"]["tmp_name"]);*/


            $CONFIG = config('wangpkg.ueditor_config');

            //图片地址前缀
            $CONFIG['imageUrlPrefix'] = '/';
            $config = array(
                "pathFormat" => $CONFIG['imagePathFormat'],
                "maxSize" => $CONFIG['imageMaxSize'],
                "allowFiles" => $CONFIG['imageAllowFiles']
            );
            /* 生成上传实例对象并完成上传 */
            $up = new Uploader('upload', $config, 'upload');

            $fileinfo = $up->getFileInfo();

            return ['uploaded' => true, 'url' => env('LOCAL_IMG_URL') . $fileinfo['url'], 'files' => $_FILES];
        } catch (Exception $e) {
            return ['uploaded' => false, 'url' => '', 'files' => $_FILES, 'message' => $e->getMessage()];
        }
    }

    public function upFile()
    {

        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        /*$allow_origin = array(
            'http://www.zhaozhaoapp.com',
            'http://dev.zhaozhaoapp.com'
        );*/
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Allow-Headers: Authorization,Origin, X-Requested-With, Content-Type, Accept');
        header('Access-Control-Allow-Methods: GET,POST');

        $user = '';

        //return ['status' => 0, 'url' => '', 'msg' => '请登录！', 'files' => $_FILES];

        if (request('updateType') != 'admin') {
            //$user = UserAuthServices::fastGetUser();
            //if (!$user) {
            return ['status' => 0, 'url' => '', 'msg' => '请登录！', 'files' => $user];
            //}
            //$uid = $user->id;
        }

        try {
            //Post变量由2M修改为8M，此值改为比upload_max_filesize要大
            ini_set('post_max_size', '120M');
            //上传文件修改也为8M，和上面这个有点关系，大小不等的关系。
            ini_set('upload_max_filesize', '100M');
            //正在运行的脚本大量使用系统可用内存,上传图片给多点，最好比post_max_size大1.5倍
            ini_set('memory_limit', '200M');


            $bucket = env('QINIU_BUCKET');
            $accessKey = env('QINIU_ACCESS_KEY');
            $secretKey = env('QINIU_SECRET_KEY');

            // 初始化签权对象
            $auth = new qiniu($accessKey, $secretKey);
            // 生成上传Token
            $token = $auth->uploadToken($bucket);
            // 构建 UploadManager 对象
            $uploadMgr = new UploadManager();

            //$pos = strpos($_FILES["upload"]["name"], '.', 1);
            //$suffix = substr($_FILES["upload"]["name"], $pos + 1);
            //$key = uniqid() . '.' . rtrim($suffix, '.');
            //$key = substr($fileinfo['url'], 1);

            $key = date('Y-m-d_H:i:s') . '_' . $_FILES["upload"]["name"];
            $uploadMgr->putFile($token, $key, $_FILES["upload"]["tmp_name"]);
            //$key
            return ['status' => 1, 'url' => 'https:' . env('QINIU_IMG_URL') . $key, 'files' => $_FILES];

        } catch (Exception $e) {
            return ['status' => 0, 'url' => 'https:' . env('QINIU_IMG_URL') . $key, 'files' => $_FILES, 'msg' => $e->getMessage()];
        }
    }

    public function editorAction()
    {
        //header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
        //header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");
        //$path = base_path('config/editor.json');
        //$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($path)), true);

        $CONFIG = config('wangpkg.ueditor_config');
        $action = $_GET['action'];

        //图片地址前缀
        $CONFIG['imageUrlPrefix'] = rtrim(env('LOCAL_IMG_URL'), '/');

        switch ($action) {
            case 'config':
                $result = json_encode($CONFIG);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = $this->action_upload($CONFIG);
                break;

            /* 列出图片 */
            case 'listimage':
                $result = $this->action_list($CONFIG);
                break;
            /* 列出文件 */
            case 'listfile':
                $result = $this->action_list($CONFIG);
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = $this->action_crawler($CONFIG);
                break;

            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

    private function action_upload($CONFIG)
    {
        /* 上传配置 */
        $base64 = "upload";
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize" => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles']
                );
                $fieldName = $CONFIG['imageFieldName'];
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $CONFIG['scrawlPathFormat'],
                    "maxSize" => $CONFIG['scrawlMaxSize'],
                    "allowFiles" => $CONFIG['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $CONFIG['scrawlFieldName'];
                $base64 = "base64";
                break;
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $CONFIG['videoPathFormat'],
                    "maxSize" => $CONFIG['videoMaxSize'],
                    "allowFiles" => $CONFIG['videoAllowFiles']
                );
                $fieldName = $CONFIG['videoFieldName'];
                break;
            case 'uploadfile':
            default:
                $config = array(
                    "pathFormat" => $CONFIG['filePathFormat'],
                    "maxSize" => $CONFIG['fileMaxSize'],
                    "allowFiles" => $CONFIG['fileAllowFiles']
                );
                $fieldName = $CONFIG['fileFieldName'];
                break;
        }

        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($fieldName, $config, $base64);

        $fileinfo = $up->getFileInfo();

        try {
            /*            $bucket = env('QINIU_BUCKET');
                        $accessKey = env('QINIU_ACCESS_KEY');
                        $secretKey = env('QINIU_SECRET_KEY');
                        // 初始化签权对象
                        $auth = new qiniu($accessKey, $secretKey);
                        // 生成上传Token
                        $token = $auth->uploadToken($bucket);
                        // 构建 UploadManager 对象
                        $uploadMgr = new UploadManager();

                        $key = substr($fileinfo['url'], 1);
                        $uploadMgr->putFile($token, $key, $up->getFilePath());*/
        } catch (Exception $e) {

        }


        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */

        /* 返回数据 */
        return json_encode($up->getFileInfo());
    }

    private function action_list($CONFIG)
    {
        /* 判断类型 */
        switch ($_GET['action']) {
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $CONFIG['fileManagerAllowFiles'];
                $listSize = $CONFIG['fileManagerListSize'];
                $path = $CONFIG['fileManagerListPath'];
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $CONFIG['imageManagerAllowFiles'];
                $listSize = $CONFIG['imageManagerListSize'];
                $path = $CONFIG['imageManagerListPath'];
        }
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "" : "/") . $path;
        $files = getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--) {
            $list[] = $files[$i];
        }
//倒序
//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
//    $list[] = $files[$i];
//}

        /* 返回数据 */
        $result = json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));

        return $result;


    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {
                        $files[] = array(
                            'url' => substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime' => filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }


    private function action_crawler($CONFIG)
    {
        set_time_limit(0);
        /* 上传配置 */
        $config = array(
            "pathFormat" => $CONFIG['catcherPathFormat'],
            "maxSize" => $CONFIG['catcherMaxSize'],
            "allowFiles" => $CONFIG['catcherAllowFiles'],
            "oriName" => "remote.png"
        );
        $fieldName = $CONFIG['catcherFieldName'];

        /* 抓取远程图片 */
        $list = array();
        if (isset($_POST[$fieldName])) {
            $source = $_POST[$fieldName];
        } else {
            $source = $_GET[$fieldName];
        }
        foreach ($source as $imgUrl) {
            $item = new Uploader($imgUrl, $config, "remote");
            $info = $item->getFileInfo();
            array_push($list, array(
                "state" => $info["state"],
                "url" => $info["url"],
                "size" => $info["size"],
                "title" => htmlspecialchars($info["title"]),
                "original" => htmlspecialchars($info["original"]),
                "source" => htmlspecialchars($imgUrl)
            ));
        }

        /* 返回抓取数据 */
        return json_encode(array(
            'state' => count($list) ? 'SUCCESS' : 'ERROR',
            'list' => $list
        ));
    }

    public function getConfig()
    {
        $path = public_path('laravel-u-editor/php/config.json');
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($path)), true);
        print_r($CONFIG);
        //$configPath = base_path('config/editor.php');
        //file_put_contents($configPath, $s);
    }

    public function getToken()
    {
        $bucket = env('QINIU_BUCKET');
        $accessKey = env('QINIU_ACCESS_KEY');
        $secretKey = env('QINIU_SECRET_KEY');
        $auth = new qiniu($accessKey, $secretKey);
        $upToken = $auth->uploadToken($bucket);
        $ret = array('uptoken' => $upToken);
        echo json_encode($ret);
    }

}
