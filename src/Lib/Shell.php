<?php

/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/11/16
 * Time: 8:59 上午
 */

namespace Wang\Pkg\Lib;

class Shell
{

    //Wang\Pkg\Lib\Shell::execArtisan('wangpkg','taskCount');
    public static function execArtisan($command = "test", $param = '')
    {
        $shell = escapeshellcmd('/usr/bin/env php ' . base_path('artisan') . ' ' . $command . ' ' . $param);
        $response = exec($shell);
        return $response;
    }

    //Wang\Pkg\Lib\Shell::execPHP();
    public static function execPHP($file = "test.js", array $param = [])
    {
        /*        $config = new \stdClass;
                $config->nginx = "1";*/
        $shell = escapeshellcmd('/usr/bin/env php ' . base_path('script/' . $file)) . ' ' . urlencode(json_encode($param));
        //$response = exec('/path/to/phantomjs myscript.js');
        $response = exec($shell);

        $result = [];

        $result['output'] = $response;

        try {
            $result['json'] = json_decode($response, true);
        } catch (\Exception $e) {
            $result['json'] = [];
        }

        return $result;
    }

    //Wang\Pkg\Lib\Shell::execJs();
    public static function execJs($jsfile = "test.js", array $param = [])
    {
        /*        $config = new \stdClass;
                $config->nginx = "1";*/
        $shell = escapeshellcmd('/usr/bin/env node ' . base_path('script/' . $jsfile)) . ' ' . urlencode(json_encode($param));
        //$response = exec('/path/to/phantomjs myscript.js');
        $response = exec($shell);

        $result = [];

        $result['output'] = $response;

        try {
            $result['json'] = json_decode($response, true);
        } catch (\Exception $e) {
            $result['json'] = [];
        }

        return $result;
    }
}
