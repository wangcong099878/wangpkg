<?php

namespace Wang\Pkg\Lib;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class EasyLog
{

    public static function errFomat(\Exception $exception)
    {
        $arr = [
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => collect($exception->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ];

        return $arr;

        //Log::channel($channel)->info($message, $arr);

    }

    public static function errLog($channel, $message, \Exception $exception)
    {
        $arr = [
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => collect($exception->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ];

        Log::channel($channel)->info($message, $arr);

    }

}
