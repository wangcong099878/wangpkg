<?php
/**
 * This file is part of Swoole.
 *
 * @link     https://www.swoole.com
 * @contact  team@swoole.com
 * @license  https://github.com/swoole/library/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Wang\Pkg\Lib\Swoole;


class ClientPool extends ConnectionPool
{
    /** @var int */
    protected $size = 64;

    /** @var PDOConfig */
    protected $config = [
        'tcpMode' => SWOOLE_SOCK_TCP,
        'client' => [
            'timeout' => 60,
            'connect_timeout' => 3,
            'write_timeout' => 60,
            'read_timeout' => 60,
        ]
    ];

    public function __construct($config = [], int $size = self::DEFAULT_SIZE)
    {
        if ($config) {
            $this->config = $config;
        }

        parent::__construct(function () {
            try {
                pool_client_connect:
                $client = new \Swoole\Coroutine\Client($this->config['tcpMode']);
                $client->set($this->config['client']);
                return $client;
            } catch (\Throwable $e) {
                print_r($e->getMessage());
                \Co::sleep(3);
                goto pool_client_connect;
            }

        }, $size);
    }
}
