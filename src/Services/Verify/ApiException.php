<?php

namespace Wang\Pkg\Services\Verify;


class ApiException extends \Exception
{
    function __construct($data = null, $errorCode = 0, $message = "", $options = [])
    {

        $this->errorMsg = $message;
        $this->errorCode = $errorCode;
        parent::__construct($message);
    }
}
