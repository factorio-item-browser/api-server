<?php

declare(strict_types=1);

/**
 * The configuration of the Factorio Item Browser itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

return [
    'factorio-item-browser' => [
        'api-server' => [
            'authorization' => [
                'key' => 'factorio-item-browser',
                'agents' => [
                    'debug' => [
                        'accessKey' => 'factorio-item-browser'
                    ]
                ]
            ],
        ]
    ]
];
