<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\Item;
use FactorioItemBrowser\Api\Server\Database\Repository\ItemRepository;

/**
 * The service class of the item database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemService extends AbstractModsAwareService
{
    /**
     * The repository of the recipes.
     * @var ItemRepository
     */
    protected $itemRepository;

    /**
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->itemRepository = $entityManager->getRepository(Item::class);
        return $this;
    }

    /**
     * Returns the items with the specified types and names.
     * @param array|string[][] $namesByTypes
     * @return array|Item[]
     */
    public function getByTypesAndNames(array $namesByTypes): array
    {
        $result = [];
        if (count($namesByTypes) > 0) {
            $result = $this->itemRepository->findByTypesAndNames(
                $namesByTypes,
                $this->modService->getEnabledModCombinationIds()
            );
        }
        return $result;
    }

    /**
     * Returns the item with the specified type and name.
     * @param string $type
     * @param string $name
     * @return Item|null
     */
    public function getByTypeAndName(string $type, string $name): ?Item
    {
        $items = $this->itemRepository->findByTypesAndNames(
            [$type => [$name]],
            $this->modService->getEnabledModCombinationIds()
        );
        return array_shift($items);
    }

    /**
     * Returns the items with the specified ids.
     * @param array|int[] $itemIds
     * @return array|Item[]
     */
    public function getByIds(array $itemIds): array
    {
        $result = [];
        if (count($itemIds) > 0) {
            foreach ($this->itemRepository->findByIds($itemIds) as $item) {
                $result[$item->getId()] = $item;
            }
        }
        return $result;
    }

    /**
     * Returns the items matching the specified keywords.
     * @param array|string[] $keywords
     * @return array|Item[]
     */
    public function getByKeywords(array $keywords): array
    {
        $result = [];
        if (count($keywords) > 0) {
            $result = $this->itemRepository->findByKeywords(
                $keywords,
                $this->modService->getEnabledModCombinationIds()
            );
        }
        return $result;
    }

    /**
     * Filters the specified recipe names to only include the actually available ones.
     * @param array|string[][] $namesByTypes
     * @return array|string[][]
     */
    public function filterAvailableTypesAndNames(array $namesByTypes): array
    {
        $result = [];
        if (count($namesByTypes) > 0) {
            $items = $this->itemRepository->findByTypesAndNames($namesByTypes);
            foreach ($items as $item) {
                $result[$item->getType()][] = $item->getName();
            }
        }
        return $result;
    }

    /**
     * Returns random items from the database.
     * @param int $numberOfItems
     * @return array|Item[]
     */
    public function getRandom(int $numberOfItems): array
    {
        $result = [];
        $items = $this->itemRepository->findRandom($numberOfItems, $this->modService->getEnabledModCombinationIds());
        foreach ($items as $item) {
            $result[$item->getId()] = $item;
        }
        return $result;
    }
}