<?php

declare(strict_types=1);

/**
 * The configuration of doctrine.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use Doctrine\DBAL\Driver\PDOMySql\Driver as PDOMySqlDriver;
use PDO;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => PDOMySqlDriver::class,
                'params' => [
                    'host'     => 'fib-as-mysql',
                    'port'     => '3306',
                    'user'     => 'docker',
                    'password' => 'docker',
                    'dbname'   => 'docker',
                    'driverOptions' => [
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                    ],
                ],
            ],
        ],
    ],
];
