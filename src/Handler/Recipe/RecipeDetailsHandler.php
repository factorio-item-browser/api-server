<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;

/**
 * The handler of the /recipe/details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDetailsHandler extends AbstractRequestHandler
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The database recipe service.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * Initializes the auth handler.
     * @param MapperManagerInterface $mapperManager
     * @param RecipeService $recipeService
     */
    public function __construct(
        MapperManagerInterface $mapperManager,
        RecipeService $recipeService
    ) {
        $this->mapperManager = $mapperManager;
        $this->recipeService = $recipeService;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return RecipeDetailsRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param RecipeDetailsRequest $request
     * @return ResponseInterface
     * @throws MapperException
     */
    protected function handleRequest($request): ResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();
        $recipeData = $this->recipeService->getDataWithNames($request->getNames(), $authorizationToken);
        $mappedRecipes = $this->mapRecipes($recipeData);
        return $this->createResponse($mappedRecipes);
    }

    /**
     * Maps the recipes to response entities.
     * @param RecipeDataCollection $recipeData
     * @return array|RecipeWithExpensiveVersion[]
     * @throws MapperException
     */
    protected function mapRecipes(RecipeDataCollection $recipeData): array
    {
        $entity = new GenericEntityWithRecipes();
        $this->mapperManager->map($recipeData, $entity);
        return $entity->getRecipes();
    }

    /**
     * Creates the response to send to the client.
     * @param array|RecipeWithExpensiveVersion[] $recipes
     * @return RecipeDetailsResponse
     */
    protected function createResponse(array $recipes): RecipeDetailsResponse
    {
        $response = new RecipeDetailsResponse();
        $response->setRecipes($recipes);
        return $response;
    }
}
