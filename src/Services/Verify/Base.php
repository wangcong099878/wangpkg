<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2019/04/24
 * Time: 15:20
 */

namespace Wang\Pkg\Services\Verify;

use \Illuminate\Support\Facades\Validator;
use \Wang\Pkg\Lib\Response;

class Base
{
    public $rule = [

    ];

    protected function boot()
    {

    }

    protected function param($key, $rules = "", array $messages = [])
    {
        return $this->make($key, $rules, $messages);
    }

    protected function make($key, $rules = "", array $messages = [])
    {
        $this->boot();


        if ($rules == "") {
            if (isset($this->rule[$key]['rules'])) {
                $rules = $this->rule[$key]['rules'];
                $messages = isset($this->rule[$key]['messages']) ? $this->rule[$key]['messages'] : [];
            } else {
                return isset($_REQUEST[$key]) ? $_REQUEST[$key] : "";
            }
        }

        if (is_array($messages) && count($messages) > 1) {
            foreach ($messages as $k => $v) {
                if (strpos($k, '.') !== false) {
                    unset($messages[$k]);
                    $messages[$key . '.' . $k] = $v;
                }
            }
        }

        $validator = Validator::make($_REQUEST, [
            $key => $rules,
        ], $messages);

        if ($validator->fails()) {
            $keys = $validator->errors()->keys();
            return Response::swoole([], 2002, $validator->errors()->first(), ['param' => $keys[0]]);
        }

        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : "";
    }

    public function __call($method, $parameters)
    {
        return $this->{$method}(...$parameters);
    }


    public static function __callStatic($method, $parameters)
    {
        return (new static)->{$method}(...$parameters);
    }
}

