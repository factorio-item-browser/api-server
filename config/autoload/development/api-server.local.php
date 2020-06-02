<?php

declare(strict_types=1);

/**
 * The configuration of the API server itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use FactorioItemBrowser\Api\Server\Constant\ConfigKey;

return [
    ConfigKey::PROJECT => [
        ConfigKey::API_SERVER => [
            ConfigKey::AGENTS => [
                [
                    ConfigKey::AGENT_NAME => 'debug',
                    ConfigKey::AGENT_ACCESS_KEY => 'debug',
                    ConfigKey::AGENT_DEMO => false,
                ],
            ],
            ConfigKey::ALLOWED_ORIGINS => [
                '#^https?://localhost(:\d+)?$#',
            ],
            ConfigKey::AUTHORIZATION => [
                ConfigKey::AUTHORIZATION_KEY => 'factorio-item-browser',
                ConfigKey::AUTHORIZATION_TOKEN_LIFETIME => 86400,
            ],
            ConfigKey::AUTO_UPDATE => [
                ConfigKey::AUTO_UPDATE_LAST_USAGE_INTERVAL => '-1 month',
                ConfigKey::AUTO_UPDATE_MAX_UPDATES => 100,
            ],
        ],
    ],
];
