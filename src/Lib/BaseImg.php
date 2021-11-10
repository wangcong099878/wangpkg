<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2021/10/28
 * Time: 7:56 下午
 */

namespace Wang\Pkg\Lib;


class BaseImg
{
    public function testimg()
    {
        $img_dir = '../public/1.jpg';//源图片路径
        $base64_string = self::imgToBase64($img_dir);//把图片给base64一下
        echo '<img src="' . $base64_string . '">';       //图片形式展示

        $path = '../public/2.jpg';//设置新生成的图片的路径 操作之前可先看下此文件应该不存在
        $base64_string = explode(',', $base64_string); //截取data:image/png;base64, 这个逗号后的字符
        $data = base64_decode($base64_string[1]);//对截取后的字符使用base64_decode进行解码
        $rs = file_put_contents($path, $data); //写入文件并保存
        if ($rs <= 0) {
            $this->error('图片转换失败');
        }

    }

    /**
     * 获取图片的Base64编码(不支持url)
     * @param $img_file 传入本地图片地址
     * @return string
     */
    public static function imgToBase64($img_file)
    {
        $img_base64 = '';
        if (file_exists($img_file)) {
            $app_img_file = $img_file; // 图片路径
            $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等

            //echo '<pre>' . print_r($img_info, true) . '</pre><br>';
            $fp = fopen($app_img_file, "r"); // 图片是否可读权限

            if ($fp) {
                $filesize = filesize($app_img_file);
                $content = fread($fp, $filesize);
                $file_content = chunk_split(base64_encode($content)); // base64编码
                switch ($img_info[2]) {           //判读图片类型
                    case 1:
                        $img_type = "gif";
                        break;
                    case 2:
                        $img_type = "jpg";
                        break;
                    case 3:
                        $img_type = "png";
                        break;
                }

                $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;//合成图片的base64编码

            }
            fclose($fp);
        }

        return $img_base64; //返回图片的base64
    }
}
