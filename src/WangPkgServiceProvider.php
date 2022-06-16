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

        //php artisan vendor:publish --provider="Wang\Pkg\WangPkgServiceProvider"
        $this->loadRoutesFrom(__DIR__.'/Routes/wangpkg.php');

        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'wangpkg');

        //is_dir()
        if(!file_exists(app_path('Console/Commands'))){
            //初始化console
            $this->publishes([
                __DIR__ . '/Commands' => app_path('Console/Commands'),
            ],'console');
        }

        //发布视图目录
        $this->publishes([
            __DIR__ . '/Resources/views' => resource_path('views/vendor/wangpkg'),
        ],'views');

        //发布配置文件
        $this->publishes([
            __DIR__.'/Config/wangpkg.php' => config_path('wangpkg.php')
        ], 'config');

        //发布静态资源
        $this->publishes([
            __DIR__ . '/Resources/assets' => public_path('vendor/wangpkg'),
        ], 'public');

        //发布静态资源
        $this->publishes([
            __DIR__.'/Database/shs' => base_path('shs/preset'),
        ], 'shs');

        //队列资源
        $this->publishes([
            __DIR__.'/QueueAction' => app_path('QueueAction'),
        ], 'shs');

        //发布注册命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Wangpkg::class,
                Console\MakeCommand::class,
                Console\DcatCommand::class,
                Console\ActionFormCommand::class,
                Console\CreateFormCommand::class,
                Console\ActionTabCommand::class,
                Console\CreateTabCommand::class,
                Console\ToolFormCommand::class,
                Console\ActionCommand::class,
                Console\ActionJsCommand::class,
                Console\ActionModalCommand::class,
                Console\ToolBatchCommand::class,
                Console\AppCommand::class,
                Console\MakeTab::class,
                Console\RunSHS::class,
                Console\TabToShs::class,
                Console\SwooleQueue::class,
                Console\NormalQueue::class,
                Console\TryAgain::class,
                //BarCommand::class,
            ]);
        }
    }
}
