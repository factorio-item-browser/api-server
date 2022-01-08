<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler of the /recipe/details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDetailsHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly MapperManagerInterface $mapperManager,
        private readonly RecipeService $recipeService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RecipeDetailsRequest $clientRequest */
        $clientRequest = $request->getParsedBody();
        $recipeData = $this->recipeService->getDataWithNames(
            Uuid::fromString($clientRequest->combinationId),
            $clientRequest->names,
        );

        $response = new RecipeDetailsResponse();
        $response->recipes = $this->mapperManager->map($recipeData, new GenericEntityWithRecipes())->recipes;

        return new ClientResponse($response);
    }
}
