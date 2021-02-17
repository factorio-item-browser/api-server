<?php

/**
 * The base configuration file.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

return [
    'doctrine' => [
        'migrations' => [
            'orm_default' => [
                'directory' => 'data/migrations',
                'name'      => 'API Server Database Migrations',
                'namespace' => 'FactorioItemBrowser\Api\Server\Migrations',
                'table'     => '_Migrations',
            ],
        ],
    ],
];
