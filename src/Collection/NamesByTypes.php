<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Collection;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * The class holding entity names grouped by their types.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class NamesByTypes implements IteratorAggregate
{
    /**
     * The values of the collection.
     * @var array|string[][]
     */
    protected $values = [];

    /**
     * Adds a type and name pair to the collection.
     * @param string $type
     * @param string $name
     * @return $this
     */
    public function addName(string $type, string $name): self
    {
        $this->values[$type][] = $name;
        return $this;
    }

    /**
     * Sets the names of the type.
     * @param string $type
     * @param array|string[] $names
     * @return $this
     */
    public function setNames(string $type, array $names): self
    {
        $this->values[$type] = $names;
        return $this;
    }

    /**
     * Returns the names of the type.
     * @param string $type
     * @return array|string[]
     */
    public function getNames(string $type): array
    {
        return $this->values[$type] ?? [];
    }

    /**
     * Returns whether the type and name pair is part of the collection.
     * @param string $type
     * @param string $name
     * @return bool
     */
    public function hasName(string $type, string $name): bool
    {
        return in_array($name, $this->values[$type] ?? [], true);
    }

    /**
     * Transforms the entity into a two-dimensional array.
     * @return array|string[][]
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * Returns the iterator for the collection.
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }
}
