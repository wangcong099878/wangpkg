<?php

namespace Wang\Pkg\Lib\Proxy;

use Wang\Pkg\Lib\Proxy\FilterInterface;
use Wang\Pkg\Lib\Proxy\Http;
use Wang\Pkg\Lib\Swoole\ClientPool;

/**
 * Author:Robert
 *
 * Class Server
 * @package Swooxy
 */
class Server
{

    /**
     * Author:Robert
     *
     * @var array
     */
    private $_options = [
        'daemonize' => false,
        'reactor_num' => 1,
        'worker_num' => 10,
        //            'backlog' => 1000,
        'max_request' => 2000,
        'buffer_output_size' => 12 * 1024 * 1024,
    ];

    /**
     * Author:Robert
     *
     * @var array
     */
    private $_client = [];


    /**
     * Author:Robert
     *
     * @var array
     */
    private $_filters = [];

    /**
     * Author:Robert
     *
     * @var \Swoole\Http\Server
     */
    public $_server;

    public $clientPool;

    const CLIENT_TIMEOUT = 60;


    /**
     * Server constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if ($options) {
            $this->_options = array_merge($this->_options, $options);
        }
    }

    /**
     * Author:Robert
     *
     * @param $filer
     */
    public function setFilter($filer)
    {
        if (is_array($filer)) {
            $this->_filters = array_merge($this->_filters, $filer);
        } else {
            $this->_filters[] = $filer;
        }
    }

    /**
     * Author:Robert
     *
     * @param string $host
     * @param string $port
     */
    public function createServer(string $host, string $port)
    {
        $this->_server = new \Swoole\Server($host, $port);
    }

    /**
     * Author:Robert
     *
     * @param $tcpMode
     * @return \Swoole\Coroutine\Client
     */
    public function createClient($tcpMode)
    {

        /*        $clientPool = new ClientPool();
                $client = $clientPool->get();
                $clientPool->put(null);*/


        $client = new \Swoole\Coroutine\Client($tcpMode);
        $client->set([
            'timeout' => self::CLIENT_TIMEOUT,
            'connect_timeout' => 3,
            'write_timeout' => self::CLIENT_TIMEOUT,
            'read_timeout' => self::CLIENT_TIMEOUT,
        ]);
        return $client;
        //        return new \Swoole\Client($tcpMode, SWOOLE_SOCK_ASYNC);
    }


    /**
     * Author:Robert
     *
     * @param $message
     */
    protected function log(string $message)
    {
        echo '[' . date('Y-m-d H:i:s') . ']' . $message . PHP_EOL;
    }

    /**
     * Author:Robert
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->_server->set(array_merge($options, $this->_options));
    }

    /**
     * Author:Robert
     *
     */
    public function onConnect()
    {
        $this->_server->on('connect', function (\Swoole\Server $server, $fd) {
            $this->log("Server connection open: {$fd}");
        });
    }

    /**
     * Author:Robert
     *
     * @param $fd
     * @param $host
     * @param $port
     * @return bool
     */
    public function connect($fd, $host, $port)
    {
        if (!$this->_client[$fd]->connect($host, $port)) {
            $this->log("Connection failed [{$this->_client[$fd]->errCode}]");
            return false;
        }
        return true;
    }

    /**
     * Author:Robert
     *
     */
    public function onReceive()
    {
        $this->_server->on('receive', function (\Swoole\Server $server, $fd, $reactor_id, $buffer) {
            //判断是否为新连接
            //if (!isset($this->_client[$fd])) {

            print_r($buffer);

            $tarr = explode(' ', $buffer, 3);

            print_r("-------------");

            $ttarr = explode(':',$tarr[1]);

            print_r($ttarr);

            //识别代理
            $host = $ttarr[0];
            $method = $tarr[0];
            $port = $ttarr[1];
            //ipv4/v6处理
            //$tcpMode = $http->isIpv6() ? SWOOLE_SOCK_TCP6 : SWOOLE_SOCK_TCP;
            $this->_client[$fd] = 1;

            //if ($this->connect($fd, $host, $port)) {
            if ($method == 'CONNECT') {
                $this->log("Tunnel - Connection established");
                //告诉客户端准备就绪，可以继续发包
                $this->_server->send($fd, "HTTP/1.1 200 Connection Established\r\n\r\n");
            } else {

                $this->log("Connection Continue");
                try {
                    $client = $this->clientPool->get();
                    $client->connect($host, $port);
                    //$this->_client[$fd]->send($buffer);
                    $client->send($buffer);
                    if ($this->_server->exist($fd)) {
                        //$this->_server->send($fd, $this->_client[$fd]->recv());
                        $this->_server->send($fd, $client->recv());
                    }
                    $client->close();
                    $this->clientPool->put($client);
                } catch (\Throwable $e) {
                    print_r($e->getMessage());
                    $this->clientPool->put(null);
                }

            }
            //}
            //}
            /*else {
                try {
                    //已连接，正常转发数据
                    $client = $this->clientPool->get();

                    if ($client) {
                        $client->send($buffer);
                        if ($this->_server->exist($fd) && $data = $client->recv()) {
                            $this->_server->send($fd, $data);
                        }
                    }
                } catch (\Throwable $e) {
                    print_r($e->getMessage());
                    $this->clientPool->put(null);
                }
            }*/
        });
    }

    public function onClose()
    {
        $this->_server->on('close', function (\Swoole\Server $server, $fd) {
            $this->log("Server connection close: {$fd}");
            unset($this->_client[$fd]);
        });
    }

    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function start()
    {
        return $this->_server->start();
    }

    /**
     * Author:Robert
     *
     * @param string $host
     * @param int $port
     * @return mixed
     */
    public function listen($host = '0.0.0.0', $port = 10080)
    {
        $this->createServer($host, $port);
        $this->setOptions();
        $this->onConnect();
        $this->onReceive();
        $this->onClose();

        $this->clientPool = new ClientPool();
        return $this->start();
    }
}
