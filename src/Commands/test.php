<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Wang\Pkg\Services\QueueServices;

const N = 1024;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test {action?} {param?} {param1?}';

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
        $param = $this->argument('param');
        call_user_func([$this, $action], $param);
    }

    //php artisan test runold
    public function runold()
    {

    }

    //php artisan test sGet
    public function sGet()
    {

    }

}
