<?php

declare(strict_types=1);

/**
 * The configuration of doctrine.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

return [
    'doctrine' => [
        'migrations_configuration' => [
            'orm_default' => [
                'directory' => 'data/migrations',
                'name'      => 'API Server Database Migrations',
                'namespace' => 'FactorioItemBrowser\Api\Server\Migrations',
                'table'     => '_Migrations',
            ],
        ],
    ],
];
