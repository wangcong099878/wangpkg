<?php

namespace Wang\Pkg\Console;


use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Wang\Pkg\Lib\ChangeTable;
use Wang\Pkg\Lib\BatchAddModel;
use Wang\Pkg\Lib\Shell;

class ChangeDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'changedb {filepath?} {savepath?} {connect?}';

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

        $basepath = base_path('tab_struct');
        //php artisan maketab '/Users/wangcong/php/pkgdev/tabStruct.tab' nginx
        $filepath = $this->argument('filepath') ? $this->argument('filepath') : 'database';

        $path = $basepath.'/'.$filepath;

        $saveBasePath = $this->argument('savepath') ? $this->argument('savepath') : 'migrations';

        if($saveBasePath == 'date'){
            $saveBasePath = date('Ymd_His');
        }

        $connect = $this->argument('connect') ? $this->argument('connect') : 'mysql';

        ChangeTable::run($path,$saveBasePath);

        //执行
        //print_r(Shell::execArtisan('migrate','--path='.$saveBasePath));
    }

}
