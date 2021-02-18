<?php

/**
 * The configuration of the API server itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use FactorioItemBrowser\Api\Server\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::AGENTS => [
            [
                ConfigKey::AGENT_NAME => 'development',
                ConfigKey::AGENT_API_KEY => 'factorio-item-browser',
            ],
        ],
        ConfigKey::ALLOWED_ORIGINS => [
            '#^https?://localhost(:\d+)?$#',
        ],
    ],
];
