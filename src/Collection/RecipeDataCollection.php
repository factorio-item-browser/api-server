<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Collection;

use FactorioItemBrowser\Api\Database\Data\RecipeData;
use Ramsey\Uuid\UuidInterface;

/**
 * The collection holding recipe data entities and offering multiple filters.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDataCollection
{
    /**
     * The values of the collection.
     * @var array<RecipeData>
     */
    protected array $values = [];

    /**
     * Adds recipe data to the collection.
     * @param RecipeData $recipeData
     * @return RecipeDataCollection
     */
    public function add(RecipeData $recipeData): self
    {
        $this->values[] = $recipeData;
        return $this;
    }

    /**
     * Returns all the recipe ids currently known to the collection.
     * @return array<UuidInterface>
     */
    public function getAllIds(): array
    {
        $result = [];
        foreach ($this->values as $recipeData) {
            $result[$recipeData->getId()->toString()] = $recipeData->getId();
        }
        return array_values($result);
    }

    /**
     * Counts the different recipe names of the collection.
     * @return int
     */
    public function countNames(): int
    {
        $result = [];
        foreach ($this->values as $recipeData) {
            $result[$recipeData->getName()] = true;
        }
        return count($result);
    }

    /**
     * Returns a new instance with the recipe mode filtered.
     * @param string $recipeMode
     * @return self
     */
    public function filterMode(string $recipeMode): self
    {
        return $this->filter(function (RecipeData $recipeData) use ($recipeMode): bool {
            return $recipeData->getMode() === $recipeMode;
        });
    }

    /**
     * Returns a new instance with the item id filtered.
     * @param UuidInterface $itemId
     * @return self
     */
    public function filterItemId(UuidInterface $itemId): self
    {
        return $this->filter(function (RecipeData $recipeData) use ($itemId): bool {
            return $recipeData->getItemId() !== null && $itemId->equals($recipeData->getItemId());
        });
    }

    /**
     * Creates a new instance with the filter applied.
     * @param callable $filter
     * @return self
     */
    protected function filter(callable $filter): self
    {
        $result = new self();
        foreach ($this->values as $recipeData) {
            if ($filter($recipeData)) {
                $result->add($recipeData);
            }
        }
        return $result;
    }

    /**
     * Returns a new instance with the names limited.
     * @param int $numberOfNames
     * @param int $indexOfFirstName
     * @return self
     */
    public function limitNames(int $numberOfNames, int $indexOfFirstName): self
    {
        $groupedValues = [];
        foreach ($this->values as $recipeData) {
            $groupedValues[$recipeData->getName()][] = $recipeData;
        }

        $result = new self();
        foreach (array_slice($groupedValues, $indexOfFirstName, $numberOfNames) as $values) {
            foreach ($values as $recipeData) {
                $result->add($recipeData);
            }
        }
        return $result;
    }

    /**
     * Returns all the data from the collection.
     * @return array<RecipeData>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Returns the first value from the collection.
     * @return RecipeData|null
     */
    public function getFirstValue(): ?RecipeData
    {
        $result = reset($this->values);
        return $result instanceof RecipeData ? $result : null;
    }
}
