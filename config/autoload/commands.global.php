<?php

declare(strict_types=1);

/**
 * The file providing the commands.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use FactorioItemBrowser\Api\Server\Constant\CommandName;

return [
    'commands' => [
        CommandName::CLEAN_CACHE => Command\CleanCacheCommand::class,
        CommandName::UPDATE_COMBINATIONS => Command\UpdateCombinationsCommand::class,
    ],
];
