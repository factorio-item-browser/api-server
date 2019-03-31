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
     * Returns the grouped recipe ids having the item as ingredient.
     * @param Item $item
     * @param AuthorizationToken $authorizationToken
     * @return RecipeDataCollection
     */
    public function getDataWithIngredient(Item $item, AuthorizationToken $authorizationToken): RecipeDataCollection
    {
        $recipeData = $this->recipeRepository->findDataByIngredientItemIds(
            [$item->getId()],
            $authorizationToken->getEnabledModCombinationIds()
        );
        return $this->createDataCollection($recipeData);
    }

    /**
     * Returns the grouped recipe ids having the item as product.
     * @param Item $item
     * @param AuthorizationToken $authorizationToken
     * @return RecipeDataCollection
     */
    public function getDataWithProduct(Item $item, AuthorizationToken $authorizationToken): RecipeDataCollection
    {
        $recipeData = $this->recipeRepository->findDataByProductItemIds(
            [$item->getId()],
            $authorizationToken->getEnabledModCombinationIds()
        );
        return $this->createDataCollection($recipeData);
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
        $result = [];
        foreach ($this->recipeRepository->findByIds($ids) as $recipe) {
            $result[$recipe->getId()] = $recipe;
        }
        return $result;
    }
}
