<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'metadata_cache' => 'filesystem',
                'query_cache' => 'filesystem',
                'numeric_functions' => [
                    'Rand' => Database\Functions\RandFunction::class,
                ]
            ]
        ],
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'FactorioItemBrowser\Api\Server\Database\Entity' => 'fib-api-server',
                ]
            ],

            'fib-api-server' => [
                'class' => AnnotationDriver::class,
                'cache' => 'filesystem',
                'paths' => [
                    __DIR__ . '/../../src/Database/Entity',
                ]
            ]
        ],
    ]
];