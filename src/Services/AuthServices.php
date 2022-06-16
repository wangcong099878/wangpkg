<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2019/05/13
 * Time: 17:19
 */

namespace Wang\Pkg\Services;

use Wang\Pkg\Lib\Jwt;
use Wang\Pkg\Lib\EasyRedis;

class AuthServices
{

    public $prefix = "api";
    public $model = \App\User::class;
    public $loginField = [];

    protected function loginField()
    {
        return $this->loginField;
    }

    protected function user($token = '')
    {

        $uid = $this->fastGetUid($token);

        if ($uid) {
            return $this->model::find($uid, $this->loginField);
        }
        return false;
    }

    protected function fastGetUser($token = '')
    {

        $uid = $this->fastGetUid($token);

        if ($uid) {
            return $this->model::find($uid, $this->loginField);
        }
        return false;
    }

    protected function fastGetUid($token = '')
    {
        $key = $this->prefix . config('wangpkg.JWT_SECRET');
        $jwt = new Jwt($key);

        if ($token) {
            $payload = $jwt->verifyToken($token);
            return isset($payload['sub']) ? $payload['sub'] : false;
        }

        if ($token = isset($_REQUEST[$this->prefix . '_token']) ? $_REQUEST[$this->prefix . '_token'] : "") {
            $payload = $jwt->verifyToken($token);
            return isset($payload['sub']) ? $payload['sub'] : false;
        }


        if ($token = isset($_COOKIE[$this->prefix . '_token']) ? $_COOKIE[$this->prefix . '_token'] : "") {

            $payload = $jwt->verifyToken($token);
            return isset($payload['sub']) ? $payload['sub'] : false;
        }

        $http_auth = request()->header('authorization') ? request()->header('authorization') : "";
        $eTag = explode(' ', $http_auth);
        if (isset($eTag[0]) && $eTag[0] == 'bearer' && isset($eTag[1])) {
            $token = $eTag[1];
            $payload = $jwt->verifyToken($token);
            return isset($payload['sub']) ? $payload['sub'] : false;
        }


    }

    protected function refresh($uid, $token)
    {
        $this->getStorage($uid, $token);
    }

    protected function getUserTab($token = '')
    {
        $uid = $this->userId($token);
        if ($uid) {
            $user = $this->model::find($uid, $this->loginField);
            return $user;
        }
        return false;
    }

    protected function getToken()
    {
        $key = $this->prefix . config('wangpkg.JWT_SECRET');
        $jwt = new Jwt($key);

        if ($token = isset($_REQUEST[$this->prefix . '_token']) ? $_REQUEST[$this->prefix . '_token'] : "") {
            $payload = $jwt->verifyToken($token);
            if (isset($payload['sub'])) {
                return [
                    'uid' => $payload['sub'],
                    'token' => $token
                ];
            }
        }

        if ($token = isset($_COOKIE[$this->prefix . '_token']) ? $_COOKIE[$this->prefix . '_token'] : "") {
            $payload = $jwt->verifyToken($token);
            return isset($payload['sub']) ? $payload['sub'] : false;
        }

        $http_auth = request()->header('authorization') ? request()->header('authorization') : "";
        $eTag = explode(' ', $http_auth);
        if (isset($eTag[0]) && $eTag[0] == 'bearer' && isset($eTag[1])) {
            $token = $eTag[1];
            $payload = $jwt->verifyToken($token);

            //return isset($payload['sub']) ? $payload['sub'] : false;
            if (isset($payload['sub'])) {
                return [
                    'uid' => $payload['sub'],
                    'token' => $token
                ];
            }
        }

        return [
            'uid' => $payload['sub'],
            'token' => $token
        ];

    }


    protected function token()
    {

        if ($token = isset($_REQUEST[$this->prefix . '_token']) ? $_REQUEST[$this->prefix . '_token'] : "") {
            return $token;
        }

        $http_auth = request()->header('authorization') ? request()->header('authorization') : "";

        $eTag = explode(' ', $http_auth);
        if (isset($eTag[0]) && $eTag[0] == 'bearer' && isset($eTag[1])) {
            $token = $eTag[1];
            return $token;
        }

        if ($token = isset($_COOKIE[$this->prefix . '_token']) ? $_COOKIE[$this->prefix . '_token'] : "") {
            return $token;
        }

        return '';
    }


    protected function userId($token = '')
    {
        if ($token) {
            $payload = $this->check($token);
            return isset($payload['sub']) ? $payload['sub'] : false;
        }


        if ($token = isset($_REQUEST[$this->prefix . '_token']) ? $_REQUEST[$this->prefix . '_token'] : "") {
            $payload = $this->check($token);
            return isset($payload['sub']) ? $payload['sub'] : false;
        }

        if ($token = isset($_COOKIE[$this->prefix . '_token']) ? $_COOKIE[$this->prefix . '_token'] : "") {
            $payload = $this->check($token);
            return isset($payload['sub']) ? $payload['sub'] : false;
        }

        $http_auth = request()->header('authorization') ? request()->header('authorization') : "";

        $eTag = explode(' ', $http_auth);
        if (isset($eTag[0]) && $eTag[0] == 'bearer' && isset($eTag[1])) {
            $token = $eTag[1];
            $payload = $this->check($token);

            return isset($payload['sub']) ? $payload['sub'] : false;
        }
    }

    //登录
    protected function login($user)
    {
        $uid = $user->id;
        $token = $this->create($uid);

        $result = [
            'Token' => $token,
            'Authorization' => 'bearer ' . $token,
            'Expira' => config('wangpkg.JWT_TTL')
        ];
        return $result;
    }

    //使用uid登录
    protected function idLogin($uid)
    {
        $token = $this->create($uid);

        $result = [
            'Token' => $token,
            'Authorization' => 'bearer ' . $token,
            'Expira' => config('wangpkg.JWT_TTL')
        ];
        return $result;
    }

    protected function storage($uid, $token)
    {

        $rd = EasyRedis::getInstance();
        $ttl = config('wangpkg.JWT_TTL');
        $multiTerminal = config('wangpkg.JWT_MULTI_TERMINAL_LOGIN');

        if ($multiTerminal) {
            $rd->setex($token, $ttl, $uid);
        } else {
            $key = $this->prefix . '_' . $uid;
            $rd->setex($key, $ttl, $token);
        }

    }

    protected function getStorage($uid, $token)
    {
        $rd = EasyRedis::getInstance();
        $multiTerminal = config('wangpkg.JWT_MULTI_TERMINAL_LOGIN');

        if ($multiTerminal) {
            return $rd->get($token);
        } else {
            $key = $this->prefix . '_' . $uid;
            return $rd->get($key);
        }
    }

    protected function logout($token, $uid)
    {
        $rd = EasyRedis::getInstance();

        $multiTerminal = config('wangpkg.JWT_MULTI_TERMINAL_LOGIN');

        if ($multiTerminal) {
            return $rd->del($token);
        } else {
            $key = $this->prefix . '_' . $uid;
            return $rd->del($key);
        }
    }

    //App\Services\AuthServices::create(1);
    protected function create($uid)
    {
        $key = config('wangpkg.JWT_SECRET');
        $ttl = config('wangpkg.JWT_TTL');
        $exp = time() + $ttl;

        $payload = [
            'iss' => 'admin',
            'iat' => time(),
            'exp' => $exp,
            'nbf' => time(),
            'sub' => $uid,
            'jti' => md5(uniqid('JWT'))
        ];

        $key = $this->prefix . config('wangpkg.JWT_SECRET');

        $jwt = new Jwt($key);
        $token = $jwt->getToken($payload);
        $this->storage($uid, $token);
        return $token;
    }

    protected function newToken($uid)
    {
        $token = $this->create($uid);

        $result = [
            'Token' => $token,
            'Authorization' => 'bearer ' . $token,
            'Expira' => config('wangpkg.JWT_TTL')
        ];
        return $result;
    }

    protected function getPayload($token)
    {
        $key = $this->prefix . config('wangpkg.JWT_SECRET');
        $jwt = new Jwt($key);
        $payload = $jwt->getPayload($token);
        return $payload;
    }

    //App\Services\AuthServices::check("eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJhZG1pbiIsImlhdCI6MTU2NzUwMTM3OCwiZXhwIjoxNTY4Nzk3Mzc4LCJuYmYiOjE1Njc1MDEzNzgsInN1YiI6NiwianRpIjoiMjQzYjE2YjBhOGVjOGE0ZTM0OTk1MWM2ZjM0MGQxMjMifQ.L6N9R51vo18F2LtnREKn07tx7xcsES9dNjI67RD-KCs");
    protected function check($token)
    {
        $key = $this->prefix . config('wangpkg.JWT_SECRET');
        $jwt = new Jwt($key);
        $payload = $jwt->verifyToken($token);

        if (!$payload) {
            return $payload;
        }

        $uid = $payload['sub'];

        $check_token = $this->getStorage($uid, $token);

        //判断多终端登录
        $multiTerminal = config('wangpkg.JWT_MULTI_TERMINAL_LOGIN');

        if ($multiTerminal) {
            if ($uid == $check_token) {
                return $payload;
            } else {
                return false;
            }
        } else {
            if ($token == $check_token) {
                return $payload;
            } else {
                return false;
            }
        }
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
