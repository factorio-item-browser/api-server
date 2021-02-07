<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerAwareTrait;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Common\Constant\RecipeMode;

/**
 * The class able to map grouped recipe ids to a generic entity with recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements StaticMapperInterface<RecipeDataCollection, GenericEntityWithRecipes>
 */
class RecipeDataCollectionToGenericEntityWithRecipesMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;

    private RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    public function getSupportedSourceClass(): string
    {
        return RecipeDataCollection::class;
    }

    public function getSupportedDestinationClass(): string
    {
        return GenericEntityWithRecipes::class;
    }

    /**
     * @param RecipeDataCollection $source
     * @param GenericEntityWithRecipes $destination
     */
    public function map(object $source, object $destination): void
    {
        $databaseRecipes = $this->recipeService->getDetailsByIds($source->getAllIds());

        $normalRecipes = $this->mapNormalRecipes($databaseRecipes, $source->filterMode(RecipeMode::NORMAL));
        $destination->recipes = $this->mapExpensiveRecipes(
            $databaseRecipes,
            $normalRecipes,
            $source->filterMode(RecipeMode::EXPENSIVE),
        );
    }

    /**
     * @param array<string, DatabaseRecipe> $databaseRecipes
     * @param RecipeDataCollection $recipeData
     * @return array<string, RecipeWithExpensiveVersion>
     */
    protected function mapNormalRecipes(array $databaseRecipes, RecipeDataCollection $recipeData): array
    {
        $recipes = [];
        foreach ($recipeData->getValues() as $data) {
            $databaseRecipe = $databaseRecipes[$data->getId()->toString()] ?? null;
            if ($databaseRecipe === null) {
                continue;
            }

            $recipe = $this->mapperManager->map($databaseRecipe, new RecipeWithExpensiveVersion());
            $recipes[$recipe->name] = $recipe;
        }
        return $recipes;
    }

    /**
     * @param array<string, DatabaseRecipe> $databaseRecipes
     * @param array<string, RecipeWithExpensiveVersion> $normalRecipes
     * @param RecipeDataCollection $recipeData
     * @return array<string, RecipeWithExpensiveVersion>
     */
    protected function mapExpensiveRecipes(
        array $databaseRecipes,
        array $normalRecipes,
        RecipeDataCollection $recipeData
    ): array {
        $recipes = [];
        foreach ($recipeData->getValues() as $data) {
            $databaseRecipe = $databaseRecipes[$data->getId()->toString()] ?? null;
            if ($databaseRecipe === null) {
                continue;
            }

            $expensiveRecipe = $this->mapperManager->map($databaseRecipe, new RecipeWithExpensiveVersion());
            if (isset($normalRecipes[$expensiveRecipe->name])) {
                $recipe = $normalRecipes[$expensiveRecipe->name];
                $recipe->expensiveVersion = $expensiveRecipe;
                $recipes[$expensiveRecipe->name] = $recipe;
            } else {
                $recipes[$expensiveRecipe->name] = $expensiveRecipe;
            }
        }
        return $recipes;
    }
}
