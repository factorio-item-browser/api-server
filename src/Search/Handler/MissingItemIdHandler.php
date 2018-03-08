<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Search\Result\ItemResult;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The search handler adding missing item IDs to the already existing search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MissingItemIdHandler implements SearchHandlerInterface
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
        $namesByTypes = [];
        foreach ($searchResults->getResults() as $searchResult) {
            if ($searchResult instanceof ItemResult && $searchResult->getId() === 0) {
                $namesByTypes[$searchResult->getType()][] = $searchResult->getName();
            }
        }

        $items = $this->itemService->getByTypesAndNames($namesByTypes);
        foreach ($items as $item) {
            $result = new ItemResult();
            $result
                ->setId($item->getId())
                ->setType($item->getType())
                ->setName($item->getName());
            $searchResults->add($result);
        }

        return $this;
    }
}