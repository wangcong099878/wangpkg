<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2021/1/14
 * Time: 3:27 下午
 */

namespace Wang\Pkg\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ShowController extends Controller
{
    public function db(Request $request)
    {
        return view('wangpkg::index', ['msg' => config('wangpkg.pagename')]);
    }
}
