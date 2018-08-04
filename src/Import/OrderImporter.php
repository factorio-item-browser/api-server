<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;

/**
 * The class updating the orders of mods and combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class OrderImporter implements ImporterInterface
{
    /**
     * The database service of the mods.
     * @var ModService
     */
    protected $modService;

    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The order of the mods.
     * @var array|int[]
     */
    protected $modOrders;

    /**
     * Initializes the importer.
     * @param ModService $modService
     * @param ExportDataService $exportDataService
     */
    public function __construct(ModService $modService, ExportDataService $exportDataService)
    {
        $this->modService = $modService;
        $this->exportDataService = $exportDataService;
    }

    /**
     * Imports the mod.
     * @param ExportMod $exportMod
     * @param DatabaseMod $databaseMod
     * @return $this
     */
    public function importMod(ExportMod $exportMod, DatabaseMod $databaseMod)
    {
        $databaseMods = $this->modService->getAllMods();

        $order = 1;
        foreach ($this->exportDataService->getMods() as $exportMod) {
            if (isset($databaseMods[$exportMod->getName()])) {
                $databaseMods[$exportMod->getName()]->setOrder($order);
                ++$order;
            }
        }

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
        $this->modOrders = $this->getModOrders();
        $combinations = $this->modService->getAllCombinations();
        uasort($combinations, function (ModCombination $left, ModCombination $right): int {
            $result = $left->getMod()->getOrder() <=> $right->getMod()->getOrder();
            if ($result === 0) {
                $leftOrders = $this->mapModIdsToOrders($left->getOptionalModIds());
                $rightOrders = $this->mapModIdsToOrders($right->getOptionalModIds());
                $result = count($leftOrders) <=> count($rightOrders);
                while ($result === 0 && !empty($leftOrders)) {
                    $result = array_shift($leftOrders) <=> array_shift($rightOrders);
                }
            }
            return $result;
        });

        $order = 1;
        foreach ($combinations as $combination) {
            $combination->setOrder($order);
            ++$order;
        }

        return $this;
    }

    /**
     * Returns the orders of the mods.
     * @return array|int[]
     */
    protected function getModOrders(): array
    {
        $result = [];
        foreach ($this->modService->getAllMods() as $mod) {
            $result[$mod->getId()] = $mod->getOrder();
        }
        return $result;
    }

    /**
     * Maps the mod ids to their orders.
     * @param array|int[] $modIds
     * @return array|int[]
     */
    protected function mapModIdsToOrders(array $modIds): array
    {
        $result = [];
        foreach ($modIds as $modId) {
            $result[] = $this->modOrders[$modId];
        }
        sort($result);
        return $result;
    }

    /**
     * Cleans up any no longer needed data.
     * @return $this
     */
    public function clean()
    {
        return $this;
    }
}
