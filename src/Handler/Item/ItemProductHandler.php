<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Item\ItemProductRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemProductResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;

/**
 * The handler of the /item/product request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemProductHandler extends AbstractItemRecipeHandler
{
    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return ItemProductRequest::class;
    }

    /**
     * Fetches the recipe data to the specified item.
     * @param DatabaseItem $item
     * @return RecipeDataCollection
     */
    protected function fetchRecipeData(DatabaseItem $item): RecipeDataCollection
    {
        return $this->recipeService->getDataWithProducts([$item], $this->getAuthorizationToken());
    }

    /**
     * Creates the response to the specified item.
     * @param GenericEntityWithRecipes $item
     * @return ResponseInterface
     */
    protected function createResponse(GenericEntityWithRecipes $item): ResponseInterface
    {
        $result = new ItemProductResponse();
        $result->setItem($item);
        return $result;
    }
}
