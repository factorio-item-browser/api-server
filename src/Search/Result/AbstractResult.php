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
     * The IDs of the recipes attached to the search result.
     * @var array|int[]
     */
    protected $recipeIds = [];

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
     * Sets the IDs of the recipes attached to the search result.
     * @param array|int[] $recipeIds
     * @return $this
     */
    public function setRecipeIds(array $recipeIds)
    {
        $this->recipeIds = array_filter(array_map('intval', $recipeIds));
        return $this;
    }

    /**
     * Adds the ID of a recipe attached to the search result.
     * @param int $recipeId
     * @return $this
     */
    public function addRecipeId(int $recipeId)
    {
        $this->recipeIds[] = $recipeId;
        return $this;
    }

    /**
     * Returns the IDs of the recipes attached to the search result.
     * @return array|int[]
     */
    public function getRecipeIds()
    {
        return $this->recipeIds;
    }

    /**
     * Returns whether the specified ID of a recipe is attached to the search result.
     * @param int $recipeId
     * @return bool
     */
    public function hasRecipeId(int $recipeId)
    {
        return in_array($recipeId, $this->recipeIds);
    }
}