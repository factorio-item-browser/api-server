<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Database\Repository\RecipeRepository;

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
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->recipeRepository = $entityManager->getRepository(Recipe::class);
        return $this;
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
        $result = [];
        if (count($names) > 0) {
            $recipeData = $this->recipeRepository->findIdDataByNames(
                $names,
                $this->modService->getEnabledModCombinationIds()
            );

            if (count($this->modService->getEnabledModCombinationIds()) > 0) {
                $recipeData = $this->filterData($recipeData, ['name', 'mode']);
            }
            foreach ($recipeData as $data) {
                $result[$data['name']][] = (int) $data['id'];
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
     * @return array|int[][]
     */
    public function getIdsWithIngredients(array $itemIds): array
    {
        $result = [];
        if (count($itemIds) > 0) {
            $recipeData = $this->recipeRepository->findIdDataWithIngredientItemId(
                $itemIds,
                $this->modService->getEnabledModCombinationIds()
            );
            foreach ($this->filterData($recipeData, ['itemId', 'name', 'mode']) as $data) {
                $result[(int) $data['itemId']][$data['name']][] = $data['id'];
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
        $result = [];
        if (count($itemIds) > 0) {
            $recipeData = $this->recipeRepository->findIdDataWithProductItemId(
                $itemIds,
                $this->modService->getEnabledModCombinationIds()
            );
            foreach ($this->filterData($recipeData, ['itemId', 'name', 'mode']) as $data) {
                $result[(int) $data['itemId']][$data['name']][] = $data['id'];
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
        if (count($ids) > 0) {
            $result = array_combine($ids, array_fill(0, count($ids), null));

            foreach ($this->recipeRepository->findByIds($ids) as $recipe) {
                $result[$recipe->getId()] = $recipe;
            }
        }
        return $result;
    }

    /**
     * Filters the specified recipe names to only include the actually available ones.
     * @param array|string[] $names
     * @return array|string[]
     */
    public function filterAvailableNames(array $names): array
    {
        $result = [];
        if (count($names) > 0) {
            $recipeData = $this->recipeRepository->findIdDataByNames(
                $names,
                $this->modService->getEnabledModCombinationIds()
            );
            foreach ($recipeData as $data) {
                $result[$data['name']] = true;
            }
        }
        return array_keys($result);
    }

    /**
     * Returns the items matching the specified keywords.
     * @param array|string[] $keywords
     * @return array
     */
    public function getIdDataByKeywords(array $keywords): array
    {
        $results = [];
        if (count($keywords) > 0) {
            $results = $this->recipeRepository->findIdDataByKeywords(
                $keywords,
                $this->modService->getEnabledModCombinationIds()
            );
            $results = $this->filterData($results, ['name', 'mode']);
        }
        return $results;
    }

    /**
     * Removes any orphaned recipes, i.e. recipes no longer used by any combination.
     * @return $this
     */
    public function removeOrphans()
    {
        $this->recipeRepository->removeOrphans();
        return $this;
    }
}
