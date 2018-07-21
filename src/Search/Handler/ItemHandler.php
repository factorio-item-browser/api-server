<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Search\Result\ItemResult;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\Result\ResultPriority;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The search handler matching the names of items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemHandler implements SearchHandlerInterface
{
    /**
     * The database item service.
     * @var ItemService
     */
    protected $itemService;

    /**
     * Initializes the search handler.
     * @param ItemService $itemService
     */
    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * Searches for anything matching the specified query.
     * @param SearchQuery $searchQuery
     * @param ResultCollection $searchResults
     * @return $this
     */
    public function handle(SearchQuery $searchQuery, ResultCollection $searchResults)
    {
        foreach ($this->itemService->getByKeywords($searchQuery->getKeywords()) as $item) {
            $searchResult = new ItemResult();
            $searchResult
                ->setId($item->getId())
                ->setType($item->getType())
                ->setName($item->getName())
                ->setPriority(ResultPriority::EXACT_MATCH);
            $searchResults->add($searchResult);
        }
        return $this;
    }
}
