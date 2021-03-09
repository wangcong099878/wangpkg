<?php

namespace Wang\Pkg;

use Illuminate\Support\ServiceProvider;

class WangPkgServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        //print_r("发布资源");
        //php artisan vendor:publish --provider="Wang\Pkg\WangPkgServiceProvider"
        $this->loadRoutesFrom(__DIR__.'/Routes/wangpkg.php');

        $this->loadViewsFrom(__DIR__.'/Resources/views', 'wangpkg');

        //发布视图目录
        $this->publishes([
            __DIR__.'/Resources/views' => resource_path('views/vendor/wangpkg'),
        ],'views');

        //发布配置文件
        $this->publishes([
            __DIR__.'/Config/wangpkg.php' => config_path('wangpkg.php')
        ], 'config');

        //发布静态资源
        $this->publishes([
            __DIR__.'/Resources/assets' => public_path('vendor/wangpkg'),
        ], 'public');

        //发布静态资源
        $this->publishes([
            __DIR__.'/Database/tab_struct' => base_path('tab_struct/preset'),
        ], 'tab_struct');

        //发布注册命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\wangpkg::class,
                Console\MakeCommand::class,
                Console\MakeTab::class,
                Console\ChangeDB::class,
                Console\TabToTxt::class,
                //BarCommand::class,
            ]);
        }
    }
}
