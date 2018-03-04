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
     * Returns the item with the specified type and name.
     * @param string $type
     * @param string $name
     * @return Item|null
     */
    public function getByTypeAndName(string $type, string $name): ?Item
    {
        $items = $this->itemRepository->findIdDataByTypesAndNames(
            [$type => [$name]],
            $this->modService->getEnabledModCombinationIds()
        );
        return array_shift($items);
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
            $items = $this->itemRepository->findIdDataByTypesAndNames($namesByTypes);
            foreach ($items as $item) {
                $result[$item->getType()][] = $item->getName();
            }
        }
        return $result;
    }
}