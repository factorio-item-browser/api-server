<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Search\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\Result\ResultPriority;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The search handler matching the names of recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeHandler implements SearchHandlerInterface
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
        foreach ($this->recipeService->getIdDataByKeywords($searchQuery->getKeywords()) as $recipeData) {
            $searchResult = new RecipeResult();
            $searchResult->addRecipeIds($recipeData['name'], [$recipeData['id']])
                         ->setName($recipeData['name'])
                         ->setPriority(ResultPriority::EXACT_MATCH);
            $searchResults->add($searchResult);
        }
        return $this;
    }
}