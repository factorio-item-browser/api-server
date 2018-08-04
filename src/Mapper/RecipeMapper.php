<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Constant\RecipeMode;
use FactorioItemBrowser\Api\Client\Entity\Item as ClientItem;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;

/**
 * The class able to map recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMapper extends AbstractMapper
{
    /**
     * Maps the database recipe into the specified client recipe.
     * @param DatabaseRecipe $databaseRecipe
     * @param ClientRecipe $clientRecipe
     * @return ClientRecipe
     */
    public function mapRecipe(DatabaseRecipe $databaseRecipe, ClientRecipe $clientRecipe): ClientRecipe
    {
        $clientRecipe->setName($databaseRecipe->getName())
                     ->setMode($databaseRecipe->getMode())
                     ->setCraftingTime($databaseRecipe->getCraftingTime());

        foreach ($databaseRecipe->getOrderedIngredients() as $databaseIngredient) {
            $clientItem = new ClientItem();
            $clientItem->setName($databaseIngredient->getItem()->getName())
                       ->setType($databaseIngredient->getItem()->getType())
                       ->setAmount($databaseIngredient->getAmount());
            $clientRecipe->addIngredient($clientItem);

            $this->translationService->addEntityToTranslate($clientItem);
        }

        foreach ($databaseRecipe->getOrderedProducts() as $databaseProduct) {
            $clientItem = new ClientItem();
            $clientItem->setName($databaseProduct->getItem()->getName())
                       ->setType($databaseProduct->getItem()->getType())
                       ->setAmount($databaseProduct->getAmount());
            $clientRecipe->addProduct($clientItem);

            $this->translationService->addEntityToTranslate($clientItem);
        }

        $this->translationService->addEntityToTranslate($clientRecipe);
        return $clientRecipe;
    }

    /**
     * Combines the new recipe with the existing one, so that the expensive recipe will be attached to the normal one.
     * @param RecipeWithExpensiveVersion $existingRecipe
     * @param ClientRecipe $newRecipe
     * @return $this
     */
    public function combineRecipes(RecipeWithExpensiveVersion $existingRecipe, ClientRecipe $newRecipe)
    {
        if ($newRecipe->getMode() === RecipeMode::EXPENSIVE) {
            $existingRecipe->setExpensiveVersion($newRecipe);
        } else {
            $expensiveData = $existingRecipe->writeData();
            $existingRecipe->readData(new DataContainer($newRecipe->writeData()));
            $newRecipe->readData(new DataContainer($expensiveData));
        }
        return $this;
    }
}
