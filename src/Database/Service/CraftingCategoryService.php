<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Server\Database\Repository\CraftingCategoryRepository;

/**
 * The service class of the crafting category database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CraftingCategoryService extends AbstractDatabaseService
{
    /**
     * The repository of the crafting categories.
     * @var CraftingCategoryRepository
     */
    protected $craftingCategoryRepository;

    /**
     * The cache of already known entities.
     * @var array|CraftingCategory[]
     */
    protected $cachedCraftingCategories = [];

    /**
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->craftingCategoryRepository = $entityManager->getRepository(CraftingCategory::class);
        return $this;
    }

    /**
     * Returns the crafting categories with the specified name, if known.
     * @param array|string[] $names
     * @return array|CraftingCategory[]
     */
    public function getByNames(array $names): array
    {
        $this->fetchMissingCraftingCategories($names);
        return array_intersect_key($this->cachedCraftingCategories, array_flip($names));
    }

    /**
     * Returns the crafting category with the specified name, if known.
     * @param string $name
     * @return CraftingCategory|null
     */
    public function getByName(string $name): ?CraftingCategory
    {
        $this->fetchMissingCraftingCategories([$name]);
        return $this->cachedCraftingCategories[$name] ?? null;
    }

    /**
     * Reads the crafting categories with the specified names into the cache.
     * @param array|string[] $names
     * @return $this
     */
    protected function fetchMissingCraftingCategories(array $names)
    {
        $missingNames = array_diff($names, array_keys($this->cachedCraftingCategories));
        if (count($missingNames) > 0) {
            foreach ($this->craftingCategoryRepository->findByNames($names) as $craftingCategory) {
                $this->cachedCraftingCategories[$craftingCategory->getName()] = $craftingCategory;
            }
        }
        return $this;
    }
}
