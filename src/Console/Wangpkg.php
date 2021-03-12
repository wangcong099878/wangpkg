<?php

namespace Wang\Pkg\Console;


use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Wang\Pkg\Lib\ManageDB;

class Wangpkg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg {action?} {param?} {param1?}';

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
        $action = $this->argument('action');

        if ($action == '') {
            echo "请输入操作名！";
            return;
        }

        $param = $this->argument('param');
        call_user_func([$this, $action], $param);
    }

    public function createModel($tabName){
        ManageDB::addModel($tabName, true, true);
        print_r("create ok! \n");
    }

    //php artisan wangpkg taskCount
    public function taskCount()
    {
        echo 12345678;
    }

}
