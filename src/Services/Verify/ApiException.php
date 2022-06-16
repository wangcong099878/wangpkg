<?php

namespace Wang\Pkg\Services\Verify;


class ApiException extends \Exception
{
    function __construct($data = null, $errorCode = 0, $message = "", $options = [])
    {
        $this->data = $data;
        $this->err = $errorCode;
        $this->message = $message;
        $this->options = $options;
        parent::__construct($message);
    }
}
