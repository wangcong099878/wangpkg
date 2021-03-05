<?php

namespace Wang\Pkg\Console;


use Illuminate\Console\Command;

/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2021/3/1
 * Time: 5:56 下午
 */

use Wang\Pkg\Lib\BatchAddModel;
use Wang\Pkg\Lib\ChangeTable;

class GenerateMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gm {filepath?} {savepath?} {--exclude=} {--choice=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        /*        Route::get('/foo', function () {
                    $exitCode = Artisan::call('email:send', [
                        'user' => 1, '--queue' => 'default'
                    ]);

                    //
                    Artisan::call('email:send 1 --queue=default');
                    //
                });*/

        //执行队列
        /*        Route::get('/foo', function () {
                    Artisan::queue('email:send', [
                        'user' => 1, '--queue' => 'default'
                    ]);

                    //
                });*/


        //排除  exclude
        //选择  choice

        $exclude = $this->option('exclude') ? $this->option('exclude') : '';
        $choice = $this->option('choice') ? $this->option('choice') : '';

        $excludeList = [];
        if ($exclude) {
            $excludeList = explode(',', $exclude);
        }


        $choiceList = [];
        if ($choice) {
            $choiceList = explode(',', $choice);
        }


        //忽略一些表

        //选择一些表

        //echo 123456;
        #生成结构目录
        #/Users/wangcong/php/pkgdev/database/日期
        //读取所有表
        $tablist = BatchAddModel::getTables();

        $tabStr = "";
        foreach ($tablist as $k => $tabName) {

            //过滤部分表 php artisan gm --exclude=admin_menu
            if ($excludeList && in_array($tabName, $excludeList)) {
                continue;
            }

            //如果选择了部分表 php artisan gm --choice=admin_menu
            if ($choiceList) {
                if (!in_array($tabName, $choiceList)) {
                    continue;
                }

            }

            $tabStr .= ChangeTable::createMigration($tabName);
            $tabStr .= "---\n";
        }

        $suffix = '';
        if(count($choiceList)==1){
            $suffix = '_'.$choiceList[0];
        }

        $datetime = date('Y_m_d_His');

        $savename = $datetime .$suffix. '.txt';


        if (!file_exists(base_path('tab_struct'))) {
            mkdir(base_path('tab_struct'));
        }

        $savepath = base_path('tab_struct/' . $savename);

        file_put_contents($savepath, $tabStr, FILE_APPEND);

        echo "php artisan changetab '{$savename}' $datetime \n";
    }

}
