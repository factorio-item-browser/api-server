<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

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
            $tempRecipe = clone($existingRecipe);
            $this->mapRecipe($newRecipe, $existingRecipe);
            $this->mapRecipe($tempRecipe, $newRecipe);
            $existingRecipe->setExpensiveVersion($newRecipe);
        }
    }

    /**
     * Maps the recipe from one instance to another.
     * @param Recipe $source
     * @param Recipe $destination
     */
    protected function mapRecipe(Recipe $source, Recipe $destination): void
    {
        $destination->setName($source->getName())
                    ->setMode($source->getMode())
                    ->setCraftingTime($source->getCraftingTime())
                    ->setIngredients($source->getIngredients())
                    ->setProducts($source->getProducts());
    }
}
