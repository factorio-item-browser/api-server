<?php
/**
 * The local configuration of the API server. This file will be replaced during deployment.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

return [
    'factorio-item-browser' => [
        'api-server' => [
            'authorization' => [
                'key' => 'factorio-item-browser',
                'agents' => [
                    'debug' => 'factorio-item-browser'
                ]
            ]
        ]
    ]
];