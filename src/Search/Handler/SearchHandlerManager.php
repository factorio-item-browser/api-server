<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Handler;

use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The manager of the search handlers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchHandlerManager
{
    /**
     * The search handlers to use.
     * @var array|SearchHandlerInterface[]
     */
    protected $handlers;

    /**
     * Initializes the search manager.
     * @param array $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * Executes the search handlers.
     * @param SearchQuery $searchQuery
     * @param ResultCollection $searchResults
     * @return ResultCollection
     */
    public function handle(SearchQuery $searchQuery, ResultCollection $searchResults)
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($searchQuery, $searchResults);
        }
        return $searchResults;
    }
}
