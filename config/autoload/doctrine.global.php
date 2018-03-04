<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'FactorioItemBrowser\Api\Server\Database\Entity' => 'fib-api-server',
                ]
            ],

            'fib-api-server' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../../src/Entity',
                ]
            ]
        ],
        'types' => [
            'flags' => Database\Type\Flags::class
        ],
    ]
];