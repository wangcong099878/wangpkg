<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/05/13
 * Time: 17:19
 */

namespace Wang\Pkg\Services;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use App\Models\QueueError;
use Wang\Pkg\Lib\Response;
use App\Models\Queue;
use Wang\Pkg\Lib\Request;
use Wang\Pkg\Lib\Ulid;


class QueueServices
{

    //暂停某个队列全部

    //销毁队列

    //暂停队列

    public static function addErr($ulid, $err)
    {
        QueueError::create([
            'ulid' => $ulid,
            'error_reason' => $err
        ]);
    }

    public static function getErrNum($ulid)
    {
        return Queue::where('ulid', $ulid)->count();
    }

    //media_id = $param1   phone = $param2
    //Wang\Pkg\Services\QueueServices::add('test',['test'=>'test1']);
    public static function add($taskName, $content = [], $param1 = '', $param2 = '')
    {
        $ulid = Ulid::generate();

        $data = [
            'taskname' => $taskName,
            'ulid' => $ulid,
            'day' => date('Y-m-d'),
            'state' => 1,
            'error_reason' => '',
            'error_num' => 0,
            'param1' => $param1,
            'param2' => $param2,
            'content' => $content,
        ];

        $queue = Queue::create($data);
        return $queue;

        /*        $queue = new Queue();
                $ulid = Ulid::generate();

                $queue->taskname = $taskName;
                $queue->ulid = $ulid;
                $queue->day = date('Y-m-d');
                $queue->state = 1;
                $queue->error_reason = '';
                $queue->error_num = 0;
                $queue->param1 = $param1;
                $queue->param2 = $param2;
                $queue->content = $content;

                $queue->save();


                return $queue;*/

    }

}
