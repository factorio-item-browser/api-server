<?php

/**
 * The config for the mapper manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use BluePsyduck\MapperManager\Constant\ConfigKey;

return [
    ConfigKey::MAIN => [
        ConfigKey::MAPPERS => [
            Mapper\DatabaseItemToGenericEntityMapper::class,
            Mapper\DatabaseMachineToClientMachineMapper::class,
            Mapper\DatabaseMachineToGenericEntityMapper::class,
            Mapper\DatabaseModToClientModMapper::class,
            Mapper\DatabaseRecipeToClientRecipeMapper::class,
            Mapper\DatabaseRecipeToGenericEntityMapper::class,
            Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper::class,
            Mapper\RecipeDataToGenericEntityMapper::class,
        ],
    ],
];
