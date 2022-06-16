<?php

namespace Wang\Pkg\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class EnableCrossRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    //php artisan make:middleware EnableCrossRequestMiddleware
    public function handle($request, Closure $next)
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Allow-Headers: Authorization,Origin, X-Requested-With, Content-Type, Accept,'.trim(env('ALLOW_HEADERS'),','));
        header('Access-Control-Allow-Methods: GET,POST,OPTIONS');
        header('Access-Control-Max-Age: 1728000');

        //如果是非简单请求 会发送options请求  非简单请求的CORS请求，会在正式通信之前，增加一次HTTP查询请求，称为"预检"请求（preflight）。
        if ($request->isMethod('OPTIONS')) {
            $response = new Response("", 200);
        } else {
            $response = $next($request);
        }

        return $response;

        /*注册全局中间件  /app/Http/Kernel.php
             protected $middleware = [
            // more
            \Wang\Pkg\Http\Middleware\EnableCrossRequestMiddleware::class,
    ];
         */

    }
}
