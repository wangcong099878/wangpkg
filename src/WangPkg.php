<?php

namespace Wang\Pkg;


/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2021/3/18
 * Time: 3:26 下午
 */
class WangPkg
{
    public function showversion()
    {
        return 1;
    }

    //可选接口
    public function apiRouters()
    {

    }

    /**
     * Register the laravel-admin builtin routes.
     *
     * @return void
     */
    public function routes()
    {
        $attributes = [
            'prefix' => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        app('router')->group($attributes, function ($router) {

            $router->namespace('\Wang\Pkg\Http\Admin\Controllers')->group(function ($router) {
                $router->resource('wangpkg/config', 'ConfigureController')->names('wangpkg.config');
                $router->resource('wangpkg/version', 'VersionController')->names('wangpkg.version');
                $router->resource('wangpkg/queue', 'QueueController')->names('wangpkg.queue');
                $router->resource('wangpkg/queue_error', 'QueueErrorController')->names('wangpkg.queue');
            });


            /* @var \Illuminate\Support\Facades\Route $router */
            /*$router->namespace('\Encore\Admin\Controllers')->group(function ($router) {

                /* @var \Illuminate\Routing\Router $router */
            /*$router->resource('auth/users', 'UserController')->names('admin.auth.users');
            $router->resource('auth/roles', 'RoleController')->names('admin.auth.roles');
            $router->resource('auth/permissions', 'PermissionController')->names('admin.auth.permissions');
            $router->resource('auth/menu', 'MenuController', ['except' => ['create']])->names('admin.auth.menu');
            $router->resource('auth/logs', 'LogController', ['only' => ['index', 'destroy']])->names('admin.auth.logs');

            $router->post('_handle_form_', 'HandleController@handleForm')->name('admin.handle-form');
            $router->post('_handle_action_', 'HandleController@handleAction')->name('admin.handle-action');
            $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('admin.handle-selectable');
            $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('admin.handle-renderable');
        });8/

        //$authController = config('admin.auth.controller', AuthController::class);

        /* @var \Illuminate\Routing\Router $router */
            /*$router->get('auth/login', $authController.'@getLogin')->name('admin.login');
            $router->post('auth/login', $authController.'@postLogin');
            $router->get('auth/logout', $authController.'@getLogout')->name('admin.logout');
            $router->get('auth/setting', $authController.'@getSetting')->name('admin.setting');
            $router->put('auth/setting', $authController.'@putSetting');*/
        });
    }

}
