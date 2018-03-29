<?php
/**
 * The configuration file of the Factorio item Browser itself..
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

return [
    'factorio-item-browser' => [
        'api-server' => [
            'authorization' => [
                'key' => 'factorio-item-browser',
                'agents' => [
                    'demo' => [
                        'accessKey' => 'factorio-item-browser',
                        'allowImport' => false,
                        'isDemo' => true
                    ]
                ]
            ]
        ],
        'export-data' => [
            'directory' => __DIR__ . '/../../data/export'
        ]
    ]
];