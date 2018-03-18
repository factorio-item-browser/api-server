<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Item as ClientItem;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;

/**
 * The class able to map recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMapper
{
    /**
     * Maps the specified database recipe to a client recipe instance.
     * @param DatabaseRecipe $databaseRecipe
     * @param TranslationService $translationService
     * @return ClientRecipe
     */
    static public function mapDatabaseRecipeToClientRecipe(
        DatabaseRecipe $databaseRecipe,
        TranslationService $translationService
    ): ClientRecipe {
        $clientRecipe = new ClientRecipe();
        $clientRecipe->setName($databaseRecipe->getName())
            ->setMode($databaseRecipe->getMode())
            ->setCraftingTime($databaseRecipe->getCraftingTime());

        foreach ($databaseRecipe->getOrderedIngredients() as $databaseIngredient) {
            $clientItem = new ClientItem();
            $clientItem->setName($databaseIngredient->getItem()->getName())
                ->setType($databaseIngredient->getItem()->getType())
                ->setAmount($databaseIngredient->getAmount());
            $clientRecipe->addIngredient($clientItem);
            $translationService->addEntityToTranslate($clientItem);
        }

        foreach ($databaseRecipe->getOrderedProducts() as $databaseProduct) {
            $clientItem = new ClientItem();
            $clientItem->setName($databaseProduct->getItem()->getName())
                ->setType($databaseProduct->getItem()->getType())
                ->setAmount($databaseProduct->getAmount());
            $clientRecipe->addProduct($clientItem);
            $translationService->addEntityToTranslate($clientItem);
        }

        $translationService->addEntityToTranslate($clientRecipe);
        return $clientRecipe;
    }
}