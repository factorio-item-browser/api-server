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
        $result = [];
        if (count($names) > 0) {
            $recipeData = $this->recipeRepository->findIdDataByNames(
                $names,
                $this->modService->getEnabledModCombinationIds()
            );

            foreach($this->filterData($recipeData, ['name', 'mode']) as $data) {
                $result[] = $data['id'];
            }
        }
        return $result;
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
            foreach($this->filterData($recipeData, ['name', 'mode']) as $data) {
                $result[$data['name']][] = $data['id'];
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
}