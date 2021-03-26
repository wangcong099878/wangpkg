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

    //media_id = $param1   phone = $param2
    //Wang\Pkg\Services\QueueServices::add(['test'=>'test1']);
    //Wang\Pkg\Services\QueueServices::add(['taskname'=>'swoole']);
    public static function add( $content = [], $param1 = '', $param2 = '',$taskName='normal')
    {
        $ulid = Ulid::generate();

        if(isset($content['taskname']) && $content['taskname']!=''){
            $taskName = $content['taskname'];
        }

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

    }

}
