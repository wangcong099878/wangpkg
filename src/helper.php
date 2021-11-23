<?php

if (!function_exists('showVersion')) {
    function showVersion()
    {
        return 'wangpkg master';
    }
}

if (!function_exists('phoneHide')) {
    function phoneHide($phone)
    {
        //$newMobile2 = substr_replace($mobile, '****', 5, 4);
        return substr($phone, 0, 5) . '****' . substr($phone, 9);
    }
}

if (!function_exists('getAdminUrl')) {
    function getAdminUrl($path)
    {
        $adminPath = config('admin.route.prefix');
        return url('/' . $adminPath . $path);
    }
}

if (!function_exists('params')) {
    function params($key, $rules = "", array $messages = [])
    {
        return Wang\Pkg\Services\VerifyServices::make($key, $rules, $messages);
    }
}

if (!function_exists('moneyReg')) {
    function moneyReg($money)
    {
        $money_reg = '/^[1-9]+\d*(.\d{1,2})?$|^\d+.\d{1,2}$/';
        if (!preg_match($money_reg, $money)) {
            return false;
        }

        return true;
    }
}

if (!function_exists('getImgs')) {
    /*
     * $content 文章内容
     * $order 要获取哪张图片，ALL所有图片，0第一张图片
    */
    function getImgs($content, $order = 'ALL')
    {
        /*$pattern = "/<img .*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/";*/
        $pattern = "/<img .*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png|\?imageView2\/1]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $content, $match);
        if (isset($match[1]) && !empty($match[1])) {
            if ($order === 'ALL') {
                return $match[1];
            }
            if (is_numeric($order) && isset($match[1][$order])) {
                return $match[1][$order];
            }
        }
        return '';
    }
}

if (!function_exists('array_sort')) {
    /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys 要排序的键字段
     * @param string $sort 排序类型  SORT_ASC     SORT_DESC
     * @return array 排序后的数组
     */
    function array_sort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param string $title
     * @param string $separator
     * @param string $language
     * @return string
     */
    function str_slug($title, $separator = '-', $language = 'en')
    {
        return \Illuminate\Support\Str::slug($title, $separator, $language);
    }
}

if (!function_exists('msectime')) {
    /**
     * 获取毫秒时间
     */
    function msectime()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (int)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}

if (!function_exists('isMobileRequest')) {
    function isMobileRequest()
    {
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
        $mobile_browser = '0';
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
            $mobile_browser++;
        if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false))
            $mobile_browser++;
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
            $mobile_browser++;
        if (isset($_SERVER['HTTP_PROFILE']))
            $mobile_browser++;
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda', 'xda-'
        );
        if (in_array($mobile_ua, $mobile_agents))
            $mobile_browser++;
        if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
            $mobile_browser++;
        // Pre-final check to reset everything if the user is on Windows
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
            $mobile_browser = 0;
        // But WP7 is also Windows, with a slightly different characteristic
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
            $mobile_browser++;
        if ($mobile_browser > 0)
            return true;
        else
            return false;
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        $ip = "";
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if ($ip) {
            return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : $ip;
        } else {
            return "";
        }


    }
}



