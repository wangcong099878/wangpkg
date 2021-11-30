<?php

namespace Wang\Pkg\Console;


use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Wang\Pkg\Lib\CreateTable;
use Wang\Pkg\Lib\ManageDB;


class MakeTab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maketab {filepath?} {savepath?} {param1?}';

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
        //php artisan maketab '/Users/wangcong/php/pkgdev/tabStruct.tab' nginx
        $filepath = $this->argument('filepath');
        $saveBasePath = $this->argument('savepath') ? $this->argument('savepath') : date('Ymd_His');

        CreateTable::run($filepath,$saveBasePath);
    }

}
