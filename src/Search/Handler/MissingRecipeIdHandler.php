<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Search\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The search handler adding missing recipe IDs to the already existing search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MissingRecipeIdHandler implements SearchHandlerInterface
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
        /* @var RecipeResult[] $recipeResults */
        $recipeResults = [];
        $recipeNames = [];
        foreach ($searchResults->toArray() as $result) {
            if ($result instanceof RecipeResult && $result->getId() === 0) {
                $recipeNames[] = $result->getName();
                $recipeResults[] = $result;
            }
        }

        $groupedRecipeIds = $this->recipeService->getIdsByNames($recipeNames);
        foreach ($recipeResults as $recipeResult) {
            if (isset($groupedRecipeIds[$recipeResult->getName()])) {
                foreach ($groupedRecipeIds[$recipeResult->getName()] as $recipeId) {
                    $recipeResult->addRecipeId($recipeId);
                }
            }
        }

        return $this;
    }
}