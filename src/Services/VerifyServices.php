<?php

/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/04/24
 * Time: 15:20
 */

namespace Wang\Pkg\Services;

use Wang\Pkg\Services\Verify\Base;
use \Illuminate\Support\Facades\Validator;
use Wang\Pkg\Lib\Response;

class VerifyServices extends Base
{
    public $rule = [
        'phone' => [
            'rules' => 'required|size:11',
            'messages' => [
                'phone.required' => '手机号不可为空', 'phone.size' => '手机号长度必须是11位'
            ]
        ],
        'password' => [
            'rules' => 'required|max:14|min:7',
            'messages' => [
                'phone.required' => '密码不可为空', 'phone.size' => '密码长度必须是7-14位'
            ]
        ],
        'code' => [
            'rules' => 'required|size:6',
            'messages' => [
                'code.required' => '验证码不可为空', 'code.size' => '长度必须是6位'
            ]
        ],
        'title' => [
            'rules' => 'required|string|max:20|min:6',
            'messages' => [
                'title.required' => '标题不可为空', 'title.max' => '标题长度范围是6-20位','title.min' => '标题长度范围是6-20位'
            ]
        ],
        'content' => [
            'rules' => 'required',
            'messages' => [
                'content.required' => '内容不可为空'
            ]
        ]
    ];

    public function boot()
    {
/*        //校验验证码有效性
        Validator::extend('verify_code', function ($attribute, $value, $parameters, $validator) {
            try {
                return \App\Models\Codelist::check($_REQUEST['phone'], $value);
            } catch (\Exception $e) {
                Response::halt([], 2002, $e->getMessage(), ['param' => 'code']);
            }
        });*/

    }

}

