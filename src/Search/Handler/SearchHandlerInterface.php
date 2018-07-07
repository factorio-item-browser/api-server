<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The interface of the search handlers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface SearchHandlerInterface
{
    /**
     * Searches for anything matching the specified query.
     * @param SearchQuery $searchQuery
     * @param ResultCollection $searchResults
     * @return $this
     */
    public function handle(SearchQuery $searchQuery, ResultCollection $searchResults);
}
