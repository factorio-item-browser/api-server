<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Item\ItemIngredientRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemIngredientResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;

/**
 * The handler of the /item/ingredient request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemIngredientHandler extends AbstractItemRecipeHandler
{
    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return ItemIngredientRequest::class;
    }

    /**
     * Fetches the recipe data to the specified item.
     * @param DatabaseItem $item
     * @return RecipeDataCollection
     */
    protected function fetchRecipeData(DatabaseItem $item): RecipeDataCollection
    {
        return $this->recipeService->getDataWithIngredients([$item], $this->getAuthorizationToken());
    }

    /**
     * Creates the response to the specified item.
     * @param GenericEntityWithRecipes $item
     * @return ResponseInterface
     */
    protected function createResponse(GenericEntityWithRecipes $item): ResponseInterface
    {
        $result = new ItemIngredientResponse();
        $result->setItem($item);
        return $result;
    }
}
