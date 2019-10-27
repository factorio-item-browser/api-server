<?php

declare(strict_types=1);

/**
 * The configuration of the Export Queue client.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;


use FactorioItemBrowser\ExportQueue\Client\Constant\ConfigKey;

return [
    ConfigKey::PROJECT => [
        ConfigKey::EXPORT_QUEUE_CLIENT => [
            ConfigKey::OPTIONS => [
                ConfigKey::OPTION_API_URL => 'http://fib-eq-php/',
                ConfigKey::OPTION_API_KEY => 'debug',
                ConfigKey::OPTION_TIMEOUT => 60,
            ],
        ],
    ],
];