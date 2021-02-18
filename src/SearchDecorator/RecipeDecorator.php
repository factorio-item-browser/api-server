<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
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
 * @implements SearchDecoratorInterface<RecipeResult>
 */
class RecipeDecorator implements SearchDecoratorInterface
{
    protected MapperManagerInterface $mapperManager;
    protected RecipeService $recipeService;

    /** @var array<string, UuidInterface> */
    protected array $announcedRecipeIds = [];
    /** @var array<DatabaseRecipe> */
    protected array $databaseRecipes = [];

    public function __construct(MapperManagerInterface $mapperManager, RecipeService $recipeService)
    {
        $this->mapperManager = $mapperManager;
        $this->recipeService = $recipeService;
    }

    public function getSupportedResultClass(): string
    {
        return RecipeResult::class;
    }

    public function initialize(int $numberOfRecipesPerResult): void
    {
        $this->announcedRecipeIds = [];
        $this->databaseRecipes = [];
    }

    /**
     * @param RecipeResult $searchResult
     */
    public function announce(ResultInterface $searchResult): void
    {
        foreach ([$searchResult->getNormalRecipeId(), $searchResult->getExpensiveRecipeId()] as $recipeId) {
            if ($recipeId !== null) {
                $this->announcedRecipeIds[$recipeId->toString()] = $recipeId;
            }
        }
    }

    public function prepare(): void
    {
        $this->databaseRecipes = $this->recipeService->getDetailsByIds($this->announcedRecipeIds);
    }

    /**
     * @param RecipeResult $searchResult
     * @return GenericEntityWithRecipes|null
     */
    public function decorate(ResultInterface $searchResult): ?GenericEntityWithRecipes
    {
        $entity = $this->mapRecipeWithId(
            $searchResult->getNormalRecipeId() ?? $searchResult->getExpensiveRecipeId(),
            new GenericEntityWithRecipes(),
        );
        if ($entity === null) {
            return null;
        }

        $recipe = $this->decorateRecipe($searchResult);
        if ($recipe instanceof ClientRecipe) {
            $entity->recipes[] = $recipe;
            $entity->totalNumberOfRecipes = 1;
        }
        return $entity;
    }

    /**
     * Maps the recipe result to a client recipe entity, if it actually has recipe ids set.
     * @param RecipeResult $recipeResult
     * @return RecipeWithExpensiveVersion|null
     */
    public function decorateRecipe(RecipeResult $recipeResult): ?RecipeWithExpensiveVersion
    {
        $normalRecipe = $this->mapRecipeWithId($recipeResult->getNormalRecipeId(), new RecipeWithExpensiveVersion());
        $expensiveRecipe = $this->mapRecipeWithId(
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

    /**
     * Maps the recipe with the specified id.
     * @template T of object
     * @param UuidInterface|null $recipeId
     * @param T $destination
     * @return T|null
     */
    protected function mapRecipeWithId(?UuidInterface $recipeId, object $destination): ?object
    {
        if ($recipeId === null) {
            return null;
        }
        $recipeId = $recipeId->toString();
        if (!isset($this->databaseRecipes[$recipeId])) {
            return null;
        }

        return $this->mapperManager->map($this->databaseRecipes[$recipeId], $destination);
    }
}
