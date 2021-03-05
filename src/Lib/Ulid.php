<?php
/*
 * This file is part of the ULID package.
 *
 * (c) Robin van der Vleuten <robin@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Wang\Pkg\Lib;


class Ulid
{
    // Crockford's Base32, all lowercased cause it's prettier in URLs.
    const ENCODING_CHARS = '0123456789abcdefghjkmnpqrstvwxyz';
    /**
     * @var int
     */
    private static $lastGenTime = 0;
    /**
     * @var array
     */
    private static $lastRandChars = [];
    /**
     * @var string
     */
    private $time;
    /**
     * @var string
     */
    private $randomness;
    /**
     * Constructor.
     *
     * @param string $time
     * @param string $randomness
     */
    private function __construct($time, $randomness)
    {
        $this->time = $time;
        $this->randomness = $randomness;
    }

    public static function test(){
        $str = "/4729.html";
        //$str = $_SERVER['REQUEST_URI'];
        $pattern = '/^\/(\d+)\.html/';
        if(preg_match_all($pattern, $str, $match)){
            if(isset($match[1][0]) && is_numeric($match[1][0])){
                if($match[1][0]<4730){
                    //发出301头部
                    header('HTTP/1.1 301 Moved Permanently');
                    //跳转到你希望的地址格式
                    header('Location: http://www.epbaba.com/uncategorized/'.$match[1][0].'.html');
                    exit;
                }
            }
        }
    }

    public static function txt(){
        $str = "";
        for($i=40;$i<5875;$i++){
            $str.='http://www.epbaba.com/'.$i.'.html http://www.epbaba.com/uncategorized/'.$i.'.html'."\n";
        }

        //echo $str;
        file_put_contents(ROOT_PATH.'/url.txt',$str);

    }


    /**
     * @return Ulid
     */
    public static function generate()
    {
        $now = (int) microtime(true) * 1000;
        $duplicateTime = $now === static::$lastGenTime;
        $timeChars = '';
        $randChars = '';
        for ($i = 9; $i >= 0; $i--) {
            $timeChars = static::ENCODING_CHARS[$now % 32].$timeChars;
            $now = (int) floor($now  / 32);
        }
        if (!$duplicateTime) {
            for ($i = 0; $i < 16; $i++) {
                static::$lastRandChars[$i] = random_int(0, 31);
            }
        } else {
            // If the timestamp hasn't changed since last push,
            // use the same random number, except incremented by 1.
            for ($i = 15; $i >= 0 && static::$lastRandChars[$i] === 31; $i--) {
                static::$lastRandChars[$i] = 0;
            }
            static::$lastRandChars[$i]++;
        }
        for ($i = 0; $i < 16; $i++) {
            $randChars .= static::ENCODING_CHARS[static::$lastRandChars[$i]];
        }
        $ulidn = new static($timeChars, $randChars);
        return strtoupper($ulidn);
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->time . $this->randomness;
    }
}
