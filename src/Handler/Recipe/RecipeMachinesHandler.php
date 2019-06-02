<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Machine as ClientMachine;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeMachinesRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Service\MachineService;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The handler of the /recipe/machines request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMachinesHandler extends AbstractRequestHandler
{
    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The machine service.
     * @var MachineService
     */
    protected $machineService;

    /**
     * The database service of the recipes.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * Initializes the request handler.
     * @param MachineService $machineService
     * @param MapperManagerInterface $mapperManager
     * @param RecipeService $recipeService
     */
    public function __construct(
        MachineService $machineService,
        MapperManagerInterface $mapperManager,
        RecipeService $recipeService
    ) {
        $this->machineService = $machineService;
        $this->mapperManager = $mapperManager;
        $this->recipeService = $recipeService;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return RecipeMachinesRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param RecipeMachinesRequest $request
     * @return ResponseInterface
     * @throws ApiServerException
     * @throws MapperException
     */
    protected function handleRequest($request): ResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();
        $recipe = $this->fetchRecipe($request, $authorizationToken);

        $machines = $this->fetchMachines($recipe, $authorizationToken);
        $limitedMachines = array_values(array_slice(
            $machines,
            $request->getIndexOfFirstResult(),
            $request->getNumberOfResults()
        ));

        return $this->createResponse($limitedMachines, count($machines));
    }

    /**
     * Fetches the recipe for the request.
     * @param RecipeMachinesRequest $request
     * @param AuthorizationToken $authorizationToken
     * @return Recipe
     * @throws EntityNotFoundException
     */
    protected function fetchRecipe(RecipeMachinesRequest $request, AuthorizationToken $authorizationToken): Recipe
    {
        $recipeData = $this->recipeService->getDataWithNames([$request->getName()], $authorizationToken);
        $firstData = $recipeData->getFirstValue();
        if (!$firstData instanceof RecipeData) {
            throw new EntityNotFoundException(EntityType::RECIPE, $request->getName());
        }

        $recipes = $this->recipeService->getDetailsByIds([$firstData->getId()]);
        $recipe = reset($recipes);
        if (!$recipe instanceof Recipe) {
            throw new EntityNotFoundException(EntityType::RECIPE, $request->getName());
        }

        return $recipe;
    }

    /**
     * Fetches the machines able to craft the recipe.
     * @param Recipe $recipe
     * @param AuthorizationToken $authorizationToken
     * @return array|DatabaseMachine[]
     */
    protected function fetchMachines(Recipe $recipe, AuthorizationToken $authorizationToken): array
    {
        $craftingCategory = $recipe->getCraftingCategory();

        $databaseMachines = $this->machineService->getMachinesByCraftingCategory(
            $craftingCategory,
            $authorizationToken
        );
        $filteredMachines = $this->machineService->filterMachinesForRecipe($databaseMachines, $recipe);
        $sortedMachines = $this->machineService->sortMachines($filteredMachines);
        return $sortedMachines;
    }

    /**
     * Creates the response with the machines.
     * @param array|DatabaseMachine[] $databaseMachines
     * @param int $totalNumberOfMachines
     * @return RecipeMachinesResponse
     * @throws MapperException
     */
    protected function createResponse(array $databaseMachines, int $totalNumberOfMachines): RecipeMachinesResponse
    {
        $result = new RecipeMachinesResponse();
        foreach ($databaseMachines as $databaseMachine) {
            $result->addMachine($this->mapMachine($databaseMachine));
        }
        $result->setTotalNumberOfResults($totalNumberOfMachines);
        return $result;
    }

    /**
     * Maps the database machine to a client one.
     * @param DatabaseMachine $databaseMachine
     * @return ClientMachine
     * @throws MapperException
     */
    protected function mapMachine(DatabaseMachine $databaseMachine): ClientMachine
    {
        $result = new ClientMachine();
        $this->mapperManager->map($databaseMachine, $result);
        return $result;
    }
}
