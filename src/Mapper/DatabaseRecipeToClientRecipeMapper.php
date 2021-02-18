<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\DynamicMapperInterface;
use FactorioItemBrowser\Api\Client\Transfer\Item as ClientItem;
use FactorioItemBrowser\Api\Client\Transfer\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Database\Entity\RecipeIngredient as DatabaseIngredient;
use FactorioItemBrowser\Api\Database\Entity\RecipeProduct as DatabaseProduct;

/**
 * The class able to map recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements DynamicMapperInterface<DatabaseRecipe, ClientRecipe>
 */
class DatabaseRecipeToClientRecipeMapper extends TranslationServiceAwareMapper implements DynamicMapperInterface
{
    public function supports(object $source, object $destination): bool
    {
        return $source instanceof DatabaseRecipe && $destination instanceof ClientRecipe;
    }

    /**
     * @param DatabaseRecipe $source
     * @param ClientRecipe $destination
     */
    public function map(object $source, object $destination): void
    {
        $destination->name = $source->getName();
        $destination->mode = $source->getMode();
        $destination->craftingTime = $source->getCraftingTime();
        $destination->ingredients = array_map([$this, 'createIngredient'], $source->getIngredients()->toArray());
        $destination->products = array_map([$this, 'createProduct'], $source->getProducts()->toArray());

        $this->addToTranslationService($destination);
    }

    protected function createIngredient(DatabaseIngredient $ingredient): ClientItem
    {
        $item = new ClientItem();
        $item->type = $ingredient->getItem()->getType();
        $item->name = $ingredient->getItem()->getName();
        $item->amount = $ingredient->getAmount();

        $this->addToTranslationService($item);

        return $item;
    }

    protected function createProduct(DatabaseProduct $product): ClientItem
    {
        $item = new ClientItem();
        $item->type = $product->getItem()->getType();
        $item->name = $product->getItem()->getName();
        $item->amount = $product->getAmount();

        $this->addToTranslationService($item);

        return $item;
    }
}
