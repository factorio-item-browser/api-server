<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Search\Entity\Result\RecipeResult;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use Ramsey\Uuid\UuidInterface;

/**
 * The decorator of the recipe search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @extends AbstractEntityDecorator<RecipeResult>
 */
class RecipeDecorator extends AbstractEntityDecorator
{
    protected RecipeService $recipeService;

    public function __construct(MapperManagerInterface $mapperManager, RecipeService $recipeService)
    {
        parent::__construct($mapperManager);

        $this->recipeService = $recipeService;
    }

    public function getSupportedResultClass(): string
    {
        return RecipeResult::class;
    }

    /**
     * @param RecipeResult $searchResult
     */
    public function announce(ResultInterface $searchResult): void
    {
        $this->addAnnouncedId($searchResult->getNormalRecipeId());
        $this->addAnnouncedId($searchResult->getExpensiveRecipeId());
    }

    protected function fetchDatabaseEntities(array $ids): array
    {
        return $this->recipeService->getDetailsByIds($ids);
    }

    /**
     * @param RecipeResult $searchResult
     * @return UuidInterface|null
     */
    protected function getIdFromResult(ResultInterface $searchResult): ?UuidInterface
    {
        return $searchResult->getNormalRecipeId() ?? $searchResult->getExpensiveRecipeId();
    }

    /**
     * @param RecipeResult $searchResult
     * @param GenericEntityWithRecipes $entity
     */
    protected function hydrateRecipes(ResultInterface $searchResult, GenericEntityWithRecipes $entity): void
    {
        $recipe = $this->decorateRecipe($searchResult);
        if ($recipe instanceof ClientRecipe) {
            $entity->recipes[] = $recipe;
            $entity->totalNumberOfRecipes = 1;
        }
    }

    /**
     * Maps the recipe result to a client recipe entity, if it actually has recipe ids set.
     * @param RecipeResult $recipeResult
     * @return RecipeWithExpensiveVersion|null
     */
    public function decorateRecipe(RecipeResult $recipeResult): ?RecipeWithExpensiveVersion
    {
        $normalRecipe = $this->mapEntityWithId($recipeResult->getNormalRecipeId(), new RecipeWithExpensiveVersion());
        $expensiveRecipe = $this->mapEntityWithId(
            $recipeResult->getExpensiveRecipeId(),
            new RecipeWithExpensiveVersion(),
        );

        $result = null;
        if ($normalRecipe !== null) {
            if ($expensiveRecipe !== null) {
                $normalRecipe->expensiveVersion = $expensiveRecipe;
            }
            $result = $normalRecipe;
        } elseif ($expensiveRecipe !== null) {
            $result = $expensiveRecipe;
        }
        return $result;
    }
}
