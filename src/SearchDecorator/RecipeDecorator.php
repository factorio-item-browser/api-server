<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Search\Entity\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use Ramsey\Uuid\UuidInterface;

/**
 * The decorator of the recipe search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDecorator implements SearchDecoratorInterface
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
     * The recipe ids of the announced search results.
     * @var array|UuidInterface[]
     */
    protected $recipeIds = [];

    /**
     * The recipes of the search results.
     * @var array|DatabaseRecipe[]
     */
    protected $recipes = [];

    /**
     * Initializes the decorator.
     * @param MapperManagerInterface $mapperManager
     * @param RecipeService $recipeService
     */
    public function __construct(MapperManagerInterface $mapperManager, RecipeService $recipeService)
    {
        $this->mapperManager = $mapperManager;
        $this->recipeService = $recipeService;
    }

    /**
     * Returns the result class supported by the decorator.
     * @return string
     */
    public function getSupportedResultClass(): string
    {
        return RecipeResult::class;
    }

    /**
     * Initializes the decorator.
     * @param int $numberOfRecipesPerResult
     */
    public function initialize(int $numberOfRecipesPerResult): void
    {
        $this->recipeIds = [];
        $this->recipes = [];
    }

    /**
     * Announces a search result to be decorated.
     * @param RecipeResult $recipeResult
     */
    public function announce($recipeResult): void
    {
        if ($recipeResult->getNormalRecipeId() !== null) {
            $this->recipeIds[] = $recipeResult->getNormalRecipeId();
        }
        if ($recipeResult->getExpensiveRecipeId() !== null) {
            $this->recipeIds[] = $recipeResult->getExpensiveRecipeId();
        }
    }

    /**
     * Prepares the data for the actual decoration.
     */
    public function prepare(): void
    {
        $recipeIds = array_values(array_unique(array_filter($this->recipeIds)));
        $this->recipes = $this->recipeService->getDetailsByIds($recipeIds);
    }

    /**
     * Actually decorates the search result.
     * @param RecipeResult $recipeResult
     * @return GenericEntityWithRecipes|null
     * @throws MapperException
     */
    public function decorate($recipeResult): ?GenericEntityWithRecipes
    {
        $recipeId = $this->getRecipeIdFromResult($recipeResult);
        if ($recipeId === null) {
            return null;
        }

        $recipeId = $recipeId->toString();
        if (!isset($this->recipes[$recipeId])) {
            return null;
        }

        $result = $this->createEntityForRecipe($this->recipes[$recipeId]);
        $recipe = $this->decorateRecipe($recipeResult);
        if ($recipe instanceof ClientRecipe) {
            $result->addRecipe($recipe)
                   ->setTotalNumberOfRecipes(1);
        }
        return $result;
    }

    /**
     * Creates the entity to the recipe.
     * @param DatabaseRecipe $recipe
     * @return GenericEntityWithRecipes
     * @throws MapperException
     */
    protected function createEntityForRecipe(DatabaseRecipe $recipe): GenericEntityWithRecipes
    {
        $result = new GenericEntityWithRecipes();
        $this->mapperManager->map($recipe, $result);
        return $result;
    }

    /**
     * Returns the recipe id from the result.
     * @param RecipeResult $recipeResult
     * @return UuidInterface|null
     */
    protected function getRecipeIdFromResult(RecipeResult $recipeResult): ?UuidInterface
    {
        $result = $recipeResult->getNormalRecipeId();
        if ($result === null) {
            $result = $recipeResult->getExpensiveRecipeId();
        }
        return $result;
    }

    /**
     * Maps the recipe result to a client recipe entity, if it actually has recipe ids set.
     * @param RecipeResult $recipeResult
     * @return RecipeWithExpensiveVersion|null
     * @throws MapperException
     */
    public function decorateRecipe(RecipeResult $recipeResult): ?RecipeWithExpensiveVersion
    {
        $normalRecipe = $this->mapRecipeWithId($recipeResult->getNormalRecipeId());
        $expensiveRecipe = $this->mapRecipeWithId($recipeResult->getExpensiveRecipeId());

        $result = null;
        if ($normalRecipe !== null) {
            if ($expensiveRecipe !== null) {
                $normalRecipe->setExpensiveVersion($expensiveRecipe);
            }
            $result = $normalRecipe;
        } elseif ($expensiveRecipe !== null) {
            $result = $expensiveRecipe;
        }
        return $result;
    }

    /**
     * Maps the recipe with the id.
     * @param UuidInterface|null $recipeId
     * @return RecipeWithExpensiveVersion
     * @throws MapperException
     */
    protected function mapRecipeWithId(?UuidInterface $recipeId): ?RecipeWithExpensiveVersion
    {
        if ($recipeId === null || !isset($this->recipes[$recipeId->toString()])) {
            return null;
        }

        $result = new RecipeWithExpensiveVersion();
        $this->mapperManager->map($this->recipes[$recipeId->toString()], $result);
        return $result;
    }
}
