<?php

/**
 * The configuration of doctrine.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Doctrine\DBAL\Driver\PDO\MySql\Driver as PDOMySqlDriver;
use PDO;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => PDOMySqlDriver::class,
                'params' => [
                    'host'     => 'fib-mysql',
                    'port'     => '3306',
                    'user'     => 'api',
                    'password' => 'api',
                    'dbname'   => 'api',
                    'driverOptions' => [
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                    ],
                ],
            ],
        ],
    ],
];
