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
            ConfigKey::AUTHORIZATION => [
                ConfigKey::AUTHORIZATION_KEY => 'factorio-item-browser',
            ],
        ],
    ],
];
