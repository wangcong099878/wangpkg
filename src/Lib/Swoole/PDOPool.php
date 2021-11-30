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

use PDO;

/**
 * @method PDO|PDOProxy get()
 * @method void put(PDO|PDOProxy $connection)
 */
class PDOPool extends ConnectionPool
{
    /** @var int */
    protected $size = 64;

    /** @var PDOConfig */
    protected $config;

    public function __construct(PDOConfig $config, int $size = self::DEFAULT_SIZE)
    {
        $this->config = $config;
        parent::__construct(function () {
            try {
                pool_pdo_connect:
                return new PDO(
                    "{$this->config->getDriver()}:" .
                    (
                    $this->config->hasUnixSocket() ?
                        "unix_socket={$this->config->getUnixSocket()};" :
                        "host={$this->config->getHost()};" . "port={$this->config->getPort()};"
                    ) .
                    "dbname={$this->config->getDbname()};" .
                    "charset={$this->config->getCharset()}",
                    $this->config->getUsername(),
                    $this->config->getPassword(),
                    $this->config->getOptions()
                );
            }catch(\Throwable $e){
                \Co::sleep(3);
                goto pool_pdo_connect;
            }

        }, $size, PDOProxy::class);
    }
}
