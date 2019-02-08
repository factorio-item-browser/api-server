<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\Recipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Common\Constant\RecipeMode;

/**
 * The mapper actually combining two recipes into one.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombiningRecipeMapper implements DynamicMapperInterface
{
    /**
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return $source instanceof RecipeWithExpensiveVersion && $destination instanceof Recipe;
    }

    /**
     * Maps the source object to the destination one.
     * @param RecipeWithExpensiveVersion $existingRecipe
     * @param Recipe $newRecipe
     */
    public function map($existingRecipe, $newRecipe): void
    {
        if ($newRecipe->getMode() === RecipeMode::EXPENSIVE) {
            $existingRecipe->setExpensiveVersion($newRecipe);
        } else {
            $expensiveData = $existingRecipe->writeData();
            $existingRecipe->readData(new DataContainer($newRecipe->writeData()));
            $newRecipe->readData(new DataContainer($expensiveData));
        }
    }
}
