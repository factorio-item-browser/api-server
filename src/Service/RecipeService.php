<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Filter\DataFilter;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;

/**
 * The service class of the recipe database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeService
{
    /**
     * The data filter.
     * @var DataFilter
     */
    protected $dataFilter;

    /**
     * The repository of the recipes.
     * @var RecipeRepository
     */
    protected $recipeRepository;

    /**
     * The already requested recipe details.
     * @var array|Recipe[]
     */
    protected $recipeCache = [];

    /**
     * Initializes the service.
     * @param DataFilter $dataFilter
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(DataFilter $dataFilter, RecipeRepository $recipeRepository)
    {
        $this->dataFilter = $dataFilter;
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Returns the grouped recipe ids having any of the items as ingredient.
     * @param array|Item[] $items
     * @param AuthorizationToken $authorizationToken
     * @return RecipeDataCollection
     */
    public function getDataWithIngredients(array $items, AuthorizationToken $authorizationToken): RecipeDataCollection
    {
        $recipeData = $this->recipeRepository->findDataByIngredientItemIds(
            $this->extractIdsFromItems($items),
            $authorizationToken->getEnabledModCombinationIds()
        );
        return $this->createDataCollection($recipeData);
    }

    /**
     * Returns the grouped recipe ids having any of the items as product.
     * @param array|Item[] $items
     * @param AuthorizationToken $authorizationToken
     * @return RecipeDataCollection
     */
    public function getDataWithProducts(array $items, AuthorizationToken $authorizationToken): RecipeDataCollection
    {
        $recipeData = $this->recipeRepository->findDataByProductItemIds(
            $this->extractIdsFromItems($items),
            $authorizationToken->getEnabledModCombinationIds()
        );
        return $this->createDataCollection($recipeData);
    }

    /**
     * Extracts the ids from the items.
     * @param array|Item[] $items
     * @return array|int[]
     */
    protected function extractIdsFromItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $item->getId();
        }
        return $result;
    }

    /**
     * Creates a data collection with the recipe data.
     * @param array|RecipeData[] $recipeData
     * @return RecipeDataCollection
     */
    protected function createDataCollection(array $recipeData): RecipeDataCollection
    {
        $result = new RecipeDataCollection();
        foreach ($recipeData as $data) {
            $result->add($data);
        }
        return $result;
    }

    /**
     * Returns the details of the recipes with the specified IDs.
     * @param array|int[] $ids
     * @return array|Recipe[]
     */
    public function getDetailsByIds(array $ids): array
    {
        $this->fetchRecipeDetails($ids);
        return array_intersect_key($this->recipeCache, array_flip($ids));
    }

    /**
     * Fetches the recipe details into the local cache.
     * @param array|int[] $ids
     */
    protected function fetchRecipeDetails(array $ids): void
    {
        $missingIds = array_diff($ids, array_keys($this->recipeCache));
        foreach ($this->recipeRepository->findByIds($missingIds) as $recipe) {
            $this->recipeCache[$recipe->getId()] = $recipe;
        }
    }
}
