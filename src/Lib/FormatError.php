<?php

namespace Wang\Pkg\Lib;

use Laravel\Lumen\Exceptions\Handler;
use Illuminate\Http\JsonResponse;


class FormatError extends Handler
{


    public function show($e){
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

    }

/*    public function __construct($e)
    {

    }*/

}
