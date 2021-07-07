<?php

/**
 * The base configuration file.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use FactorioItemBrowser\Api\Server\Constant\CommandName;

return [
    'commands' => [
        CommandName::CLEAN_CACHE => Command\CleanCacheCommand::class,
        CommandName::TRIGGER_COMBINATION_UPDATES => Command\TriggerCombinationUpdatesCommand::class,
    ],
];
