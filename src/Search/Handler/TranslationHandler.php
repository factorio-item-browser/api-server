<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Search\Result\ItemResult;
use FactorioItemBrowser\Api\Server\Search\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The search handler matching the translations of items and recipes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationHandler implements SearchHandlerInterface
{
    /**
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the search handler.
     * @param TranslationService $translationService
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Searches for anything matching the specified query.
     * @param SearchQuery $searchQuery
     * @param ResultCollection $searchResults
     * @return $this
     */
    public function handle(SearchQuery $searchQuery, ResultCollection $searchResults)
    {
        $resultData = $this->translationService->getTypesAndNamesByKeywords($searchQuery->getKeywords());
        foreach ($resultData as $data) {
            if ($data['type'] === EntityType::RECIPE) {
                $searchResult = new RecipeResult();
            } else {
                $searchResult = new ItemResult();
                $searchResult->setType($data['type']);
            }
            $searchResult
                ->setName($data['name'])
                ->setPriority((int) $data['priority']);

            $searchResults->add($searchResult);
        }
        return $this;
    }
}
