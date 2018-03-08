<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Result;

/**
 * The collection containing all of the search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ResultCollection
{
    /**
     * The items of the collection.
     * @var array|AbstractResult[]
     */
    protected $results;

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
     * Sorts the results of the collection.
     * @return $this
     */
    public function sort()
    {
        usort($this->results, function (AbstractResult $left, AbstractResult $right): int {
            $result =  $left->getPriority() <=> $right->getPriority();
            if ($result === 0) {
                $result = $left->getName() <=> $right->getName();
                if ($result === 0) {
                    $result = $left->getType() <=> $right->getType();
                }
            }
            return $result;
        });
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
     * Returns an array of the search results.
     * @param int $limit
     * @param int $offset
     * @return array|AbstractResult[]
     */
    public function toArray(int $limit = 0, int $offset = 0)
    {
        $result = $this->results;
        if ($limit > 0) {
            $result = array_slice($result, $offset, $limit);
        }
        return $result;
    }
}