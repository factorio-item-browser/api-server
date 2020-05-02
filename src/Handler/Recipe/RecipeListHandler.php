<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeListRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeListResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;

/**
 * The handler of the /recipe/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeListHandler extends AbstractRequestHandler
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The recipe service.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * Initializes the handler.
     * @param MapperManagerInterface $mapperManager
     * @param RecipeService $recipeService
     */
    public function __construct(MapperManagerInterface $mapperManager, RecipeService $recipeService)
    {
        $this->mapperManager = $mapperManager;
        $this->recipeService = $recipeService;
    }

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
     * @param RecipeListRequest $request
     * @return ClientResponseInterface
     * @throws MapperException
     */
    protected function handleRequest($request): ClientResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();
        $recipeData = $this->recipeService->getAllData($authorizationToken);
        $limitedRecipeData = $recipeData->limitNames($request->getNumberOfResults(), $request->getIndexOfFirstResult());
        $mappedRecipes = $this->mapRecipes($limitedRecipeData);
        return $this->createResponse($mappedRecipes, $recipeData->countNames());
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
     * @param int $numberOfRecipes
     * @return RecipeListResponse
     */
    protected function createResponse(array $recipes, int $numberOfRecipes): RecipeListResponse
    {
        $response = new RecipeListResponse();
        $response->setRecipes($recipes)
                 ->setTotalNumberOfResults($numberOfRecipes);
        return $response;
    }
}
