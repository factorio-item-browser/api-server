<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\Machine as ClientMachine;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeMachinesRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\MachineService;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Common\Constant\EntityType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The handler of the /recipe/machines request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMachinesHandler implements RequestHandlerInterface
{
    public function __construct(
        protected readonly MachineService $machineService,
        protected readonly MapperManagerInterface $mapperManager,
        protected readonly RecipeService $recipeService,
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RecipeMachinesRequest $clientRequest */
        $clientRequest = $request->getParsedBody();

        $combinationId = Uuid::fromString($clientRequest->combinationId);
        $recipe = $this->fetchRecipe($combinationId, $clientRequest->name);
        $machines = $this->fetchMachines($combinationId, $recipe);
        $limitedMachines = array_values(array_slice(
            $machines,
            $clientRequest->indexOfFirstResult,
            $clientRequest->numberOfResults,
        ));

        $response = new RecipeMachinesResponse();
        $response->machines = array_map(
            fn (DatabaseMachine $machine): ClientMachine => $this->mapperManager->map($machine, new ClientMachine()),
            $limitedMachines
        );
        $response->totalNumberOfResults = count($machines);

        return new ClientResponse($response);
    }

    /**
     * @param UuidInterface $combinationId
     * @param string $name
     * @return Recipe
     * @throws EntityNotFoundException
     */
    protected function fetchRecipe(UuidInterface $combinationId, string $name): Recipe
    {
        $recipeData = $this->recipeService->getDataWithNames($combinationId, [$name])->getFirstValue();
        if (!$recipeData instanceof RecipeData) {
            throw new EntityNotFoundException(EntityType::RECIPE, $name);
        }

        $recipes = $this->recipeService->getDetailsByIds([$recipeData->getId()]);
        $recipe = reset($recipes);
        if (!$recipe instanceof Recipe) {
            throw new EntityNotFoundException(EntityType::RECIPE, $name);
        }
        return $recipe;
    }

    /**
     * @param UuidInterface $combinationId
     * @param Recipe $recipe
     * @return array<DatabaseMachine>
     */
    protected function fetchMachines(UuidInterface $combinationId, Recipe $recipe): array
    {
        $machines = $this->machineService->getMachinesByCraftingCategory(
            $combinationId,
            $recipe->getCraftingCategory(),
        );
        $filteredMachines = $this->machineService->filterMachinesForRecipe($machines, $recipe);
        return $this->machineService->sortMachines($filteredMachines);
    }
}
