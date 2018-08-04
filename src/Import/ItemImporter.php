<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;

/**
 * The class importing the items into the database.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemImporter implements ImporterInterface
{
    /**
     * The Doctrine entity manager.
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The database service of the items.
     * @var ItemService
     */
    protected $itemService;

    /**
     * Initializes the importer.
     * @param EntityManager $entityManager
     * @param ItemService $itemService
     */
    public function __construct(EntityManager $entityManager, ItemService $itemService)
    {
        $this->entityManager = $entityManager;
        $this->itemService = $itemService;
    }

    /**
     * Imports the mod.
     * @param ExportMod $exportMod
     * @param DatabaseMod $databaseMod
     * @return $this
     */
    public function importMod(ExportMod $exportMod, DatabaseMod $databaseMod)
    {
        return $this;
    }

    /**
     * Imports the combination.
     * @param ExportCombination $exportCombination
     * @param DatabaseCombination $databaseCombination
     * @return $this
     */
    public function importCombination(ExportCombination $exportCombination, DatabaseCombination $databaseCombination)
    {
        $databaseItems = $this->getExistingItems($exportCombination);
        $databaseItems = $this->addMissingItems($exportCombination, $databaseItems);
        $this->assignItemsToCombination($databaseCombination, $databaseItems);
        return $this;
    }

    /**
     * Returns all already existing items of the combination.
     * @param ExportCombination $exportCombination
     * @return array|DatabaseItem[]
     */
    protected function getExistingItems(ExportCombination $exportCombination): array
    {
        $itemsByTypeAndName = [];
        foreach ($exportCombination->getData()->getItems() as $exportItem) {
            $itemsByTypeAndName[$exportItem->getType()][] = $exportItem->getName();
        }

        $databaseItems = [];
        foreach ($this->itemService->getByTypesAndNames($itemsByTypeAndName) as $databaseItem) {
            $databaseItems[$databaseItem->getType() . '|' . $databaseItem->getName()] = $databaseItem;
        }
        return $databaseItems;
    }

    /**
     * Adds missing items to the database.
     * @param ExportCombination $exportCombination
     * @param array|DatabaseItem[] $databaseItems
     * @return array|DatabaseItem[]
     */
    protected function addMissingItems(ExportCombination $exportCombination, array $databaseItems): array
    {
        foreach ($exportCombination->getData()->getItems() as $exportItem) {
            $key = $exportItem->getType() . '|' . $exportItem->getName();
            if (!isset($databaseItems[$key])) {
                $databaseItem = new DatabaseItem($exportItem->getType(), $exportItem->getName());
                $this->entityManager->persist($databaseItem);

                $databaseItems[$key] = $databaseItem;
            }
        }
        return $databaseItems;
    }

    /**
     * Assigns the items to the database combination.
     * @param DatabaseCombination $databaseCombination
     * @param array|DatabaseItem[] $databaseItems
     * @return $this
     */
    protected function assignItemsToCombination(DatabaseCombination $databaseCombination, array $databaseItems)
    {
        foreach ($databaseCombination->getItems() as $combinationItem) {
            $key = $combinationItem->getType() . '|' . $combinationItem->getName();
            if (isset($databaseItems[$key])) {
                unset($databaseItems[$key]);
            } else {
                $databaseCombination->getItems()->removeElement($combinationItem);
            }
        }

        foreach ($databaseItems as $databaseItem) {
            $databaseCombination->getItems()->add($databaseItem);
        }
        return $this;
    }

    /**
     * Cleans up any no longer needed data.
     * @return $this
     */
    public function clean()
    {
        $this->itemService->removeOrphans();
        return $this;
    }
}
