<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Import;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Import\ImporterManager;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\InputFilter\InputFilter;

/**
 * The handler of the /import request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ImportHandler extends AbstractRequestHandler
{
    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The database service of the mods.
     * @var ModService
     */
    protected $modService;

    /**
     * The importer manager.
     * @var ImporterManager
     */
    protected $importerManager;

    /**
     * Initializes the request handler.
     * @param ExportDataService $exportDataService
     * @param ModService $modService
     * @param ImporterManager $importerManager
     */
    public function __construct(
        ExportDataService $exportDataService,
        ModService $modService,
        ImporterManager $importerManager
    )
    {
        $this->exportDataService = $exportDataService;
        $this->modService = $modService;
        $this->importerManager = $importerManager;
    }

    /**
     * Creates the input filter to use to verify the request.
     * @return InputFilter
     */
    protected function createInputFilter(): InputFilter
    {
        return new InputFilter();
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $databaseMods = $this->modService->getAllMods();
        foreach ($this->exportDataService->getMods() as $exportMod) {
            $this->importMod($exportMod, $databaseMods[$exportMod->getName()] ?? null);

            break; // Let's try base mod only for now.
        }
        $this->importerManager->clean();

        return [];
    }

    /**
     * Imports the specified mod.
     * @param ExportMod $exportMod
     * @param DatabaseMod|null $databaseMod The database mod. Null if not yet existing.
     * @return $this
     */
    protected function importMod(ExportMod $exportMod, ?DatabaseMod $databaseMod)
    {
        if (!$databaseMod instanceof DatabaseMod) {
            $databaseMod = new DatabaseMod($exportMod->getName());
        }

        $this->importerManager->importMod($exportMod, $databaseMod);

        $databaseCombinations = [];
        foreach ($databaseMod->getCombinations() as $databaseCombination) {
            $databaseCombinations[$databaseCombination->getName()] = $databaseCombination;
        }

        // @todo We need the empty combination (no optional mods) for mod meta.
        foreach ($exportMod->getCombinations() as $exportCombination) {
            $this->importCombination(
                $exportCombination,
                $databaseMod,
                $databaseCombinations[$exportCombination->getName()] ?? null
            );
        }
        return $this;
    }

    /**
     * Imports the specified combination.
     * @param ExportCombination $exportCombination
     * @param DatabaseMod $databaseMod
     * @param DatabaseCombination|null $databaseCombination The database combination. Null if not yet existing.
     * @return $this
     */
    protected function importCombination(
        ExportCombination $exportCombination,
        DatabaseMod $databaseMod,
        ?DatabaseCombination $databaseCombination
    )
    {
        if (!$databaseCombination instanceof DatabaseCombination) {
            $databaseCombination = new DatabaseCombination($databaseMod);
            $databaseCombination->setName($exportCombination->getName());
        }

        $this->exportDataService->loadCombinationData($exportCombination);
        $this->importerManager->importCombination($exportCombination, $databaseCombination);
        return $this;
    }
}