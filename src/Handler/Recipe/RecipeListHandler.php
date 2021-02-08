<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeListRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeListResponse;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler of the /recipe/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeListHandler implements RequestHandlerInterface
{
    private MapperManagerInterface $mapperManager;
    private RecipeService $recipeService;

    public function __construct(MapperManagerInterface $mapperManager, RecipeService $recipeService)
    {
        $this->mapperManager = $mapperManager;
        $this->recipeService = $recipeService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RecipeListRequest $clientRequest */
        $clientRequest = $request->getParsedBody();

        $recipeData = $this->recipeService->getAllData(Uuid::fromString($clientRequest->combinationId));
        $limitedData = $recipeData->limitNames($clientRequest->numberOfResults, $clientRequest->indexOfFirstResult);

        $response = new RecipeListResponse();
        $response->recipes = $this->mapperManager->map($limitedData, new GenericEntityWithRecipes())->recipes;
        $response->totalNumberOfResults = $recipeData->countNames();

        return new ClientResponse($response);
    }
}
