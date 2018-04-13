<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Result;

/**
 * The minimal result collection used for the cached results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CachedResultCollection
{
    /**
     * The items of the collection.
     * @var array|AbstractResult[]
     */
    protected $results = [];

    /**
     * Adds a new result to the collection.
     * @param AbstractResult $result
     * @return $this
     */
    public function add(AbstractResult $result)
    {
        $this->results[] = $result;
        return $this;
    }

    /**
     * Returns the number of available search results.
     * @return int
     */
    public function count(): int
    {
        return count($this->results);
    }

    /**
     * Returns the search results as array.
     * @param int $limit
     * @param int $offset
     * @return array|AbstractResult[]
     */
    public function getResults(int $limit = 0, int $offset = 0)
    {
        $result = $this->results;
        if ($limit > 0) {
            $result = array_slice($result, $offset, $limit);
        }
        return array_values($result);
    }
}