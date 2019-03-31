<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use BluePsyduck\MapperManager\Exception\MapperException;
use FactorioItemBrowser\Api\Client\Request\Item\ItemIngredientRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemIngredientResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;

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
     * Creates the response data from the validated request data.
     * @param ItemIngredientRequest $request
     * @return ResponseInterface
     * @throws ApiServerException
     * @throws MapperException
     */
    protected function handleRequest($request): ResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();
        $item = $this->fetchItem($request->getType(), $request->getName(), $authorizationToken);
        $recipeData = $this->recipeService->getDataWithIngredient($item, $authorizationToken);
        $limitedRecipeData = $recipeData->limitNames($request->getNumberOfResults(), $request->getIndexOfFirstResult());

        $response = new ItemIngredientResponse();
        $response->setItem($this->createResponseEntity($item, $limitedRecipeData, $recipeData->countNames()));
        return $response;
    }
}
