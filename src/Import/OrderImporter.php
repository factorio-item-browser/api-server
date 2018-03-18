<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination as DatabaseCombination;
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
        // @todo Implement ordering of combinations.
        return $this;
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