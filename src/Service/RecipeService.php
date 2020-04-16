<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use Ramsey\Uuid\UuidInterface;

/**
 * The service class of the recipe database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeService
{
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
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Returns the recipe data having one of the names.
     * @param array|string[] $names
     * @param AuthorizationToken $authorizationToken
     * @return RecipeDataCollection
     */
    public function getDataWithNames(array $names, AuthorizationToken $authorizationToken): RecipeDataCollection
    {
        $recipeData = $this->recipeRepository->findDataByNames(
            $authorizationToken->getCombinationId(),
            $names
        );
        return $this->createDataCollection($recipeData);
    }

    /**
     * Returns the recipe data having any of the items as ingredient.
     * @param array|Item[] $items
     * @param AuthorizationToken $authorizationToken
     * @return RecipeDataCollection
     */
    public function getDataWithIngredients(array $items, AuthorizationToken $authorizationToken): RecipeDataCollection
    {
        $recipeData = $this->recipeRepository->findDataByIngredientItemIds(
            $authorizationToken->getCombinationId(),
            $this->extractIdsFromItems($items)
        );
        return $this->createDataCollection($recipeData);
    }

    /**
     * Returns the recipe data having any of the items as product.
     * @param array|Item[] $items
     * @param AuthorizationToken $authorizationToken
     * @return RecipeDataCollection
     */
    public function getDataWithProducts(array $items, AuthorizationToken $authorizationToken): RecipeDataCollection
    {
        $recipeData = $this->recipeRepository->findDataByProductItemIds(
            $authorizationToken->getCombinationId(),
            $this->extractIdsFromItems($items)
        );
        return $this->createDataCollection($recipeData);
    }

    /**
     * Extracts the ids from the items.
     * @param array|Item[] $items
     * @return array|UuidInterface[]
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
     * @param array|UuidInterface[] $recipeIds
     * @return array|Recipe[]
     */
    public function getDetailsByIds(array $recipeIds): array
    {
        $this->fetchRecipeDetails($recipeIds);

        $result = [];
        foreach ($recipeIds as $recipeId) {
            if (isset($this->recipeCache[$recipeId->toString()])) {
                $result[$recipeId->toString()] = $this->recipeCache[$recipeId->toString()];
            }
        }
        return $result;
    }

    /**
     * Fetches the recipe details into the local cache.
     * @param array|UuidInterface[] $recipeIds
     */
    protected function fetchRecipeDetails(array $recipeIds): void
    {
        $missingIds = [];
        foreach ($recipeIds as $recipeId) {
            if (!isset($this->recipeCache[$recipeId->toString()])) {
                $missingIds[] = $recipeId;
            }
        }

        foreach ($this->recipeRepository->findByIds($missingIds) as $recipe) {
            $this->recipeCache[$recipe->getId()->toString()] = $recipe;
        }
    }
}
