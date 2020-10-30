<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\Api\Client\Entity\Item as ClientItem;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Database\Entity\RecipeIngredient as DatabaseIngredient;
use FactorioItemBrowser\Api\Database\Entity\RecipeProduct as DatabaseProduct;

/**
 * The class able to map recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DatabaseRecipeToClientRecipeMapper extends TranslationServiceAwareMapper implements DynamicMapperInterface
{
    /**
     * Returns whether the mapper supports the combination of source and destination object.
     * @param object $source
     * @param object $destination
     * @return bool
     */
    public function supports($source, $destination): bool
    {
        return $source instanceof DatabaseRecipe && $destination instanceof ClientRecipe;
    }

    /**
     * Maps the source object to the destination one.
     * @param DatabaseRecipe $databaseRecipe
     * @param ClientRecipe $clientRecipe
     */
    public function map($databaseRecipe, $clientRecipe): void
    {
        $this->mapRecipe($databaseRecipe, $clientRecipe);

        foreach ($databaseRecipe->getIngredients() as $databaseIngredient) {
            $clientItem = new ClientItem();
            $this->mapIngredient($databaseIngredient, $clientItem);
            $clientRecipe->addIngredient($clientItem);
        }

        foreach ($databaseRecipe->getProducts() as $databaseProduct) {
            $clientItem = new ClientItem();
            $this->mapProduct($databaseProduct, $clientItem);
            $clientRecipe->addProduct($clientItem);
        }
    }

    /**
     * Maps the database recipe into the specified client recipe.
     * @param DatabaseRecipe $databaseRecipe
     * @param ClientRecipe $clientRecipe
     */
    protected function mapRecipe(DatabaseRecipe $databaseRecipe, ClientRecipe $clientRecipe): void
    {
        $clientRecipe->setName($databaseRecipe->getName())
                     ->setMode($databaseRecipe->getMode())
                     ->setCraftingTime($databaseRecipe->getCraftingTime());

        $this->addToTranslationService($clientRecipe);
    }

    /**
     * Maps the specified database ingredient into the client item.
     * @param DatabaseIngredient $databaseIngredient
     * @param ClientItem $clientItem
     */
    protected function mapIngredient(DatabaseIngredient $databaseIngredient, ClientItem $clientItem): void
    {
        $clientItem->setName($databaseIngredient->getItem()->getName())
                   ->setType($databaseIngredient->getItem()->getType())
                   ->setAmount($databaseIngredient->getAmount());

        $this->addToTranslationService($clientItem);
    }

    /**
     * Maps the specified database product into the client item.
     * @param DatabaseProduct $databaseProduct
     * @param ClientItem $clientItem
     */
    protected function mapProduct(DatabaseProduct $databaseProduct, ClientItem $clientItem): void
    {
        $clientItem->setName($databaseProduct->getItem()->getName())
                   ->setType($databaseProduct->getItem()->getType())
                   ->setAmount($databaseProduct->getAmount());

        $this->addToTranslationService($clientItem);
    }
}
