<?php

namespace Wang\Pkg\Console;


use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Wang\Pkg\Lib\EasyRedis;
use Wang\Pkg\Lib\ManageDB;
use App\Models\Queue;
use App\Models\QueueError;
use App\Models\QueueHistory;
use Wang\Pkg\Lib\Shell;
use Wang\Pkg\Services\QueueServices;
use Wang\Pkg\Lib\Log;

class TryAgain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wangpkg:try_again';

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
        //crontab -e   * * * * * php artisan wangpkg:try_again

        //php artisan wangpkg:try_again
        $time = time();
        $stime = $time - (5 * 60);
        $strTime = date('Y-m-d H:i:s', $stime);
        $queuelist = QueueError::where('try_again', 1)->where('created_at', '<=', $strTime)->get(['id','ulid', 'try_again']);

        foreach ($queuelist as $v) {
            $v->try_again = 2;
            $v->save();
            Queue::where('ulid',$v->ulid)->update(['state'=>1]);
            //print_r($queuelist->toArray());
        }

    }


}
