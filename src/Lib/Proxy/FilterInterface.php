<?php

namespace Wang\Pkg\Lib\Proxy;

use Wang\Pkg\Lib\Proxy\Http;


/**
 * Author:Robert
 *
 * Interface FilterInterface
 * @package Swooxy\Filter
 */
interface FilterInterface
{
    /**
     * Author:Robert
     *
     * @param Http $http
     * @return bool
     */
    public function run(Http $http): bool;

    /**
     * Author:Robert
     *
     * @return string
     */
    public function message(): string;


    /**
     * Author:Robert
     *
     * @return string
     */
    public function protocolEndTxt(): string;
}

