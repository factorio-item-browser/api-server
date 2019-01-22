<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;

/**
 * The service class of the item database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemService extends AbstractModsAwareService
{
    /**
     * The repository of the items.
     * @var ItemRepository
     */
    protected $itemRepository;

    /**
     * ItemService constructor.
     * @param ItemRepository $itemRepository
     * @param ModService $modService
     */
    public function __construct(ItemRepository $itemRepository, ModService $modService)
    {
        parent::__construct($modService);

        $this->itemRepository = $itemRepository;
    }

    /**
     * Returns the items with the specified types and names.
     * @param array|string[][] $namesByTypes
     * @return array|Item[]
     */
    public function getByTypesAndNames(array $namesByTypes): array
    {
        return $this->itemRepository->findByTypesAndNames(
            $namesByTypes,
            $this->modService->getEnabledModCombinationIds()
        );
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
        foreach ($this->itemRepository->findByIds($itemIds) as $item) {
            $result[$item->getId()] = $item;
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
        return $this->itemRepository->findByKeywords(
            $keywords,
            $this->modService->getEnabledModCombinationIds()
        );
    }

    /**
     * Filters the specified recipe names to only include the actually available ones.
     * @param array|string[][] $namesByTypes
     * @return array|string[][]
     */
    public function filterAvailableTypesAndNames(array $namesByTypes): array
    {
        $items = $this->itemRepository->findByTypesAndNames(
            $namesByTypes,
            $this->modService->getEnabledModCombinationIds()
        );

        $result = [];
        foreach ($items as $item) {
            $result[$item->getType()][] = $item->getName();
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
        $items = $this->itemRepository->findRandom($numberOfItems, $this->modService->getEnabledModCombinationIds());

        $result = [];
        foreach ($items as $item) {
            $result[$item->getId()] = $item;
        }
        return $result;
    }
}
