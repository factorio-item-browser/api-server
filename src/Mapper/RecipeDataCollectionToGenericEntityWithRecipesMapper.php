<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Mapper;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\Mapper\StaticMapperInterface;
use BluePsyduck\MapperManager\MapperManagerAwareInterface;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Common\Constant\RecipeMode;

/**
 * The class able to map grouped recipe ids to a generic entity with recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDataCollectionToGenericEntityWithRecipesMapper implements StaticMapperInterface, MapperManagerAwareInterface
{
    /**
     * The recipe service.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The database recipes.
     * @var array|DatabaseRecipe[]
     */
    protected $databaseRecipes = [];

    /**
     * Initializes the mapper.
     * @param RecipeService $recipeService
     */
    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    /**
     * Sets the mapper manager.
     * @param MapperManagerInterface $mapperManager
     */
    public function setMapperManager(MapperManagerInterface $mapperManager): void
    {
        $this->mapperManager = $mapperManager;
    }

    /**
     * Returns the source class supported by this mapper.
     * @return string
     */
    public function getSupportedSourceClass(): string
    {
        return RecipeDataCollection::class;
    }

    /**
     * Returns the destination class supported by this mapper.
     * @return string
     */
    public function getSupportedDestinationClass(): string
    {
        return GenericEntityWithRecipes::class;
    }

    /**
     * Maps the source object to the destination one.
     * @param RecipeDataCollection $recipeData
     * @param GenericEntityWithRecipes $entity
     * @throws MapperException
     */
    public function map($recipeData, $entity): void
    {
        $this->databaseRecipes = $this->recipeService->getDetailsByIds($recipeData->getAllIds());
        $recipes = $this->mapNormalRecipes($recipeData->filterMode(RecipeMode::NORMAL));
        $recipes = $this->addExpensiveRecipes($recipes, $recipeData->filterMode(RecipeMode::EXPENSIVE));
        $entity->setRecipes($recipes);
    }

    /**
     * Maps the normal recipes.
     * @param RecipeDataCollection $recipeData
     * @return array|RecipeWithExpensiveVersion[]
     * @throws MapperException
     */
    protected function mapNormalRecipes(RecipeDataCollection $recipeData): array
    {
        $result = [];
        foreach ($recipeData->getValues() as $data) {
            if (isset($this->databaseRecipes[$data->getId()])) {
                $result[$data->getName()] = $this->mapDatabaseRecipe($this->databaseRecipes[$data->getId()]);
            }
        }
        return $result;
    }

    /**
     * Adds the expensive recipes to the already mapped normal ones.
     * @param array|RecipeWithExpensiveVersion[] $recipes
     * @param RecipeDataCollection $recipeData
     * @return array|RecipeWithExpensiveVersion[]
     * @throws MapperException
     */
    protected function addExpensiveRecipes(array $recipes, RecipeDataCollection $recipeData): array
    {
        foreach ($recipeData->getValues() as $data) {
            if (isset($this->databaseRecipes[$data->getId()])) {
                $expensiveRecipe = $this->mapDatabaseRecipe($this->databaseRecipes[$data->getId()]);
                if (isset($recipes[$data->getName()])) {
                    $recipes[$data->getName()]->setExpensiveVersion($expensiveRecipe);
                } else {
                    $recipes[$data->getName()] = $expensiveRecipe;
                }
            }
        }
        return $recipes;
    }

    /**
     * Maps a database recipe to a client one.
     * @param DatabaseRecipe $recipe
     * @return RecipeWithExpensiveVersion
     * @throws MapperException
     */
    protected function mapDatabaseRecipe(DatabaseRecipe $recipe): RecipeWithExpensiveVersion
    {
        $result = new RecipeWithExpensiveVersion();
        $this->mapperManager->map($recipe, $result);
        return $result;
    }
}
