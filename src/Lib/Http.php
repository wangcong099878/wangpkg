<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Wang\Pkg\Lib;

class Http
{

    /**
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return string
     */
    protected function send_post($url, $post_data)
    {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    public static function makeUrl($url=''){
        $prefixUrl = "https:";
        if(env('NO_HTTPS')==true){
            $prefixUrl= "http:";
        }

        if (strpos($url, 'http') === false) {
            $url = $prefixUrl.$url;
        }
        return $url;
    }


    function uCurl($url, $method, $params = array(), $header = '')
    {
        $curl = curl_init();//初始化CURL句柄
        $timeout = 15;
        curl_setopt($curl, CURLOPT_URL, $url);//设置请求的URL
        curl_setopt($curl, CURLOPT_HEADER, false);// 不要http header 加快效率
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if ($header == '') {
            $header [] = "Accept-Language: zh-CN;q=0.8";
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);//设置连接等待时间

        $params = json_encode($params);

        switch ($method) {
            case "GET" :
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_NOBODY, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;//设置提交的信息
            case "PUT" :
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");

                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
        }

        $data = curl_exec($curl);//执行预定义的CURL
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);//获取http返回值
        curl_close($curl);
        $res = json_decode($data, true);//var_dump($res);
        return ['status' => $status, 'result' => $res];
    }


    public function post()
    {
        $data = array("name" => "Hagrid", "age" => "36");
        $data_string = json_encode($data);

        $ch = curl_init('http://api.local/rest/users');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
    }

    public static function curlPostJson($url, $post_data, $isjson)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($isjson) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        $output_array = json_decode($output, true);
        return $output_array;
    }


    public static function sendPost($url, $data = [], $method = 'POST', $timeout = 50, $headers = [], $connectTimeOut = 50)
    {

        //查询dns   dns_get_record

        $result = "";
        try {
            $ch = curl_init();

            if ($method == 'GET' && $data) {
                echo $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt($ch, CURLOPT_URL, $url);

            if ($method == 'POST') {
                if (is_array($data)) {
                    $data = http_build_query($data);
                }
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }

            if (substr($url, 0, 8) == "https://") {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }

            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

            //连接超时时间  不设置的话会阻塞挂起来
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);   //允许毫秒超时
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeOut);
            //curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connectTimeOut);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0");

            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }


            $result = curl_exec($ch);
            if ($errno = curl_errno($ch)) {
                $error = curl_error($ch);
                @curl_close($ch);
            }
            @curl_close($ch);
        } catch (Exception $exc) {

        }
        return $result;
    }

}
