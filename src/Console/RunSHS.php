<?php

namespace Wang\Pkg\Console;


use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Wang\Pkg\Lib\ChangeTable;
use Wang\Pkg\Lib\ManageDB;
use Wang\Pkg\Lib\Shell;

class RunSHS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runshs {filepath?} {savepath?} {connect?} {--connect=}';

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

        $basepath = base_path('shs');
        //php artisan maketab '/Users/wangcong/php/pkgdev/tabStruct.tab' nginx
        $filepath = $this->argument('filepath') ? $this->argument('filepath') : 'database';

        $path = $basepath.'/'.$filepath;

        //判断文件是否存在  如果不存在   补全后缀
        if(!file_exists($path)) {
            $path = $path.'.shs';
            //再次判断 //is_dir
            if(!file_exists($path)) {
                echo "shs文件不存在! \n";
                return "";
            }
        }

        $saveBasePath = $this->argument('savepath') ? $this->argument('savepath') : 'migrations';

        if($saveBasePath == 'date'){
            $saveBasePath = date('Ymd_His');
        }

        $connect = $this->argument('connect') ? $this->argument('connect') : 'mysql';

        ChangeTable::run($path,$saveBasePath,$connect);

        //执行
        //print_r(Shell::execArtisan('migrate','--path='.$saveBasePath));
    }

}
