<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Search\Result\ItemResult;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The search handler adding the recipes with already found product items to the results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ProductRecipeHandler implements SearchHandlerInterface
{
    /**
     * The database recipe service.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * Initializes the search handler.
     * @param RecipeService $recipeService
     */
    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    /**
     * Searches for anything matching the specified query.
     * @param SearchQuery $searchQuery
     * @param ResultCollection $searchResults
     * @return $this
     */
    public function handle(SearchQuery $searchQuery, ResultCollection $searchResults)
    {
        /* @var ItemResult[] $itemResults */
        $itemResults = [];
        foreach ($searchResults->getResults() as $result) {
            if ($result instanceof ItemResult)  {
                $itemResults[$result->getId()] = $result;
            }
        }

        $groupedRecipeIds = $this->recipeService->getIdsWithProducts(array_keys($itemResults));
        foreach ($groupedRecipeIds as $itemId => $recipeIds) {
            if (isset($itemResults[$itemId])) {
                $itemResult = $itemResults[$itemId];
                foreach (call_user_func_array('array_merge', $recipeIds) as $recipeId) {
                    $itemResult->addRecipeId($recipeId);
                }
            }
        }

        return $this;
    }
}