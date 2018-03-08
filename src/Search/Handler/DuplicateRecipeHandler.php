<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Search\Result\ItemResult;
use FactorioItemBrowser\Api\Server\Search\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The search handler for removing recipes duplicated as product recipes in items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DuplicateRecipeHandler implements SearchHandlerInterface
{
    /**
     * Searches for anything matching the specified query.
     * @param SearchQuery $searchQuery
     * @param ResultCollection $searchResults
     * @return $this
     */
    public function handle(SearchQuery $searchQuery, ResultCollection $searchResults)
    {
        /* @var RecipeResult[] $recipes */
        $recipes = [];
        /* @var ItemResult[][] $itemsByRecipeIds */
        $itemsByRecipeIds = [];

        foreach ($searchResults->toArray() as $result) {
            if ($result instanceof RecipeResult) {
                $recipes[] = $result;
            } elseif ($result instanceof ItemResult) {
                foreach ($result->getRecipeIds() as $recipeId) {
                    $itemRecipeIds[$recipeId] = true;
                    $itemsByRecipeIds[$recipeId][] = $result;
                }
            }
        }

        foreach ($recipes as $recipe) {
            if (isset($itemsByRecipeIds[$recipe->getId()])) {
                $searchResults->remove($recipe);
                foreach ($itemsByRecipeIds[$recipe->getId()] as $item) {
                    $item->setPriority(min($item->getPriority(), $recipe->getPriority()));
                }
            }
        }
        return $this;
    }
}