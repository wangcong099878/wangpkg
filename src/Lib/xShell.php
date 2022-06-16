<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/11/16
 * Time: 8:59 上午
 */

namespace Wang\Pkg\Lib;

class xShell
{
    //协程版本
    //App\Lib\xShell::execJs();
    public static function execJs($jsfile = "test.js", array $param = [])
    {
        /*        $config = new \stdClass;
                $config->nginx = "1";*/
        $shell = escapeshellcmd('/usr/bin/env node ' . base_path('script/' . $jsfile)) . ' ' . urlencode(json_encode($param));
        //$response = exec('/path/to/phantomjs myscript.js');
        $response = \Swoole\Coroutine\System::exec($shell);
        return json_decode(urldecode($response));
    }

    //协程版本
    //App\Lib\xShell::execPHP();
    public static function execPHP($command = "test", array $param = [], $isBackground = false)
    {
        if ($isBackground) {
            $shell = escapeshellcmd('nohup /usr/bin/env php ' . base_path('artisan')) . ' ' . $command . ' ' . urlencode(json_encode($param)) . " &";
        } else {
            $shell = escapeshellcmd('/usr/bin/env php ' . base_path('artisan')) . ' ' . $command . ' ' . urlencode(json_encode($param));
        }

        $response = \Swoole\Coroutine\System::exec($shell);

        $result = '';
        try {
            $result = $response['output'];
        } catch (\Exception $e) {
            $result = '';
        }

        return $result;
    }


    //协程版本
    //App\Lib\xShell::execPHP();
    public static function execPHPJson($command = "test", array $param = [], $isBackground = false)
    {
        if ($isBackground) {
            $shell = escapeshellcmd('nohup /usr/bin/env php ' . base_path('artisan')) . ' ' . $command . ' ' . urlencode(json_encode($param)) . " &";
        } else {
            $shell = escapeshellcmd('/usr/bin/env php ' . base_path('artisan')) . ' ' . $command . ' ' . urlencode(json_encode($param));
        }

        $response = \Swoole\Coroutine\System::exec($shell);

        try {
            $response['json'] = json_decode($response['output']);
        } catch (\Exception $e) {
            $response['json'] = [];
        }

        return $response;
    }
}
