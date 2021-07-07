<?php

/**
 * The configuration of the Combination API client.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server;

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey as JmsConfigKey;
use FactorioItemBrowser\CombinationApi\Client\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::BASE_URI => 'http://combination-api.fib.dev',
        ConfigKey::API_KEY => 'factorio-item-browser',
        ConfigKey::TIMEOUT => 60,
        ConfigKey::SERIALIZER => [
            JmsConfigKey::CACHE_DIR => 'data/cache/combination-api-client'
        ],
    ],
];
