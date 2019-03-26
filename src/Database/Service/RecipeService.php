<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;

/**
 * The service class of the recipe database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeService extends AbstractModsAwareService
{
    /**
     * The repository of the recipes.
     * @var RecipeRepository
     */
    protected $recipeRepository;

    /**
     * RecipeService constructor.
     * @param ModService $modService
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(ModService $modService, RecipeRepository $recipeRepository)
    {
        parent::__construct($modService);

        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Returns the IDs of the recipes with the specified names, of all modes.
     * @param array|string[] $names
     * @return array|int[]
     */
    public function getIdsByNames(array $names): array
    {
        $result = $this->getGroupedIdsByNames($names);
        if (count($result) > 0) {
            $result = call_user_func_array('array_merge', $result);
        }
        return $result;
    }

    /**
     * Returns the IDs of the recipes with the specified names, of all modes and grouped by the names.
     * @param array|string[] $names
     * @return array|int[][]
     */
    public function getGroupedIdsByNames(array $names): array
    {

        $recipeData = $this->recipeRepository->findDataByNames(
            $names,
            $this->modService->getEnabledModCombinationIds()
        );

        if (count($this->modService->getEnabledModCombinationIds()) > 0) {
            $recipeData = $this->filterData($recipeData);
        }

        $result = [];
        foreach ($recipeData as $data) {
            if ($data instanceof RecipeData) {
                $result[$data->getName()][] = $data->getId();
            }
        }
        return $result;
    }

    /**
     * Returns the ids with the specified item as an ingredient, grouped by recipe.
     * @param int $itemId
     * @return array
     */
    public function getIdsWithIngredient(int $itemId): array
    {
        $recipeIds = $this->getIdsWithIngredients([$itemId]);
        return $recipeIds[$itemId] ?: [];
    }

    /**
     * Returns the ids with one of the specified items as ingredient, grouped by recipe.
     * @param array|int[] $itemIds
     * @return array|int[][][]
     */
    public function getIdsWithIngredients(array $itemIds): array
    {
        $recipeData = $this->recipeRepository->findDataByIngredientItemIds(
            $itemIds,
            $this->modService->getEnabledModCombinationIds()
        );

        $result = [];
        foreach ($this->filterData($recipeData) as $data) {
            if ($data instanceof RecipeData) {
                $result[$data->getItemId()][$data->getName()][] = $data->getId();
            }
        }
        return $result;
    }

    /**
     * Returns the ids with the specified item as a product, grouped by recipe.
     * @param int $itemId
     * @return array
     */
    public function getIdsWithProduct(int $itemId): array
    {
        $recipeIds = $this->getIdsWithProducts([$itemId]);
        return $recipeIds[$itemId] ?: [];
    }

    /**
     * Returns the ids with one of the specified items as product, grouped by item and recipe.
     * @param array|int[] $itemIds
     * @return array|int[][][] Keys are the item id and the recipe name, and values are the recipe ids.
     */
    public function getIdsWithProducts(array $itemIds): array
    {
        $recipeData = $this->recipeRepository->findDataByProductItemIds(
            $itemIds,
            $this->modService->getEnabledModCombinationIds()
        );

        $result = [];
        foreach ($this->filterData($recipeData) as $data) {
            if ($data instanceof RecipeData) {
                $result[$data->getItemId()][$data->getName()][] = $data->getId();
            }
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
        $result = [];
        foreach ($this->recipeRepository->findByIds($ids) as $recipe) {
            $result[$recipe->getId()] = $recipe;
        }
        return $result;
    }
}
