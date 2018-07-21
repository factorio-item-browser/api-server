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
    protected $results = [];

    /**
     * Adds a new result to the collection.
     * @param AbstractResult $result
     * @return $this
     */
    public function add(AbstractResult $result)
    {
        $key = $this->getResultKey($result);
        if (isset($this->results[$key])) {
            $this->results[$key]->merge($result);
        } else {
            $this->results[$key] = $result;
        }
        return $this;
    }

    /**
     * Removes the specified result from the collection.
     * @param AbstractResult $result
     * @return $this
     */
    public function remove(AbstractResult $result)
    {
        unset($this->results[$this->getResultKey($result)]);
        return $this;
    }

    /**
     * Sorts the results of the collection.
     * @return $this
     */
    public function sort()
    {
        uasort($this->results, function (AbstractResult $left, AbstractResult $right): int {
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
     * Returns the search results as array.
     * @return array|AbstractResult[]
     */
    public function getResults()
    {
        return array_values($this->results);
    }

    /**
     * Returns the key of the specified result.
     * @param AbstractResult $result
     * @return string
     */
    protected function getResultKey(AbstractResult $result): string
    {
        return $result->getType() . '|' . $result->getName();
    }
}
