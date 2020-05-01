<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeListRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeListResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;

/**
 * The handler of the /recipe/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeListHandler extends AbstractRequestHandler
{
    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return RecipeListRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param RecipeListRequest $clientRequest
     * @return ClientResponseInterface
     */
    protected function handleRequest($clientRequest): ClientResponseInterface
    {
        return new RecipeListResponse();
    }
}
