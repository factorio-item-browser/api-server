<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Result;

/**
 * The abstract class of the search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractResult
{
    /**
     * The name of the search result.
     * @var string
     */
    protected $name = '';

    /**
     * The priority of the result.
     * @var int
     */
    protected $priority = ResultPriority::ANY_MATCH;

    /**
     * The grouped recipe ids.
     * @var array|int[][]
     */
    protected $groupedRecipeIds = [];

    /**
     * Returns the ID of the entity.
     * @return int
     */
    abstract public function getId(): int;

    /**
     * Returns the type of the search result.
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Sets the name of the search result.
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the search result.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the priority of the result.
     * @param int $priority
     * @return $this
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Returns the priority of the result.
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Adds a group of recipe ids.
     * @param string $groupName
     * @param array|int[] $recipeIds
     * @return $this
     */
    public function addRecipeIds(string $groupName, array $recipeIds)
    {
        foreach ($recipeIds as $recipeId) {
            $this->groupedRecipeIds[$groupName][$recipeId] = true;
        }
        return $this;
    }

    /**
     * Returns the grouped recipe ids.
     * @return array|int[][]
     */
    public function getGroupedRecipeIds(): array
    {
        return array_map('array_keys', $this->groupedRecipeIds);
    }

    /**
     * Returns the first recipe id of the result.
     * @return int
     */
    public function getFirstRecipeId(): int
    {
        $result = 0;
        if (count($this->groupedRecipeIds) > 0) {
            $firstRecipeIdGroup = reset($this->groupedRecipeIds);
            $result = key($firstRecipeIdGroup) ?: 0;
        }
        return $result;
    }

    /**
     * Merges the specified result into the current one.
     * @param AbstractResult $result
     * @return $this
     */
    public function merge(AbstractResult $result)
    {
        $this->priority = min($this->priority, $result->getPriority());
        foreach ($result->getGroupedRecipeIds() as $groupName => $recipeIds) {
            $this->addRecipeIds($groupName, $recipeIds);
        }
        return $this;
    }
}
