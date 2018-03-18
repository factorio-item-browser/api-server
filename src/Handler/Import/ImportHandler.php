<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Import;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Import\ImporterManager;
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
        $importMods = $this->exportDataService->getMods();

        foreach ($importMods as $importMod) {
            if (isset($databaseMods[$importMod->getName()])) {
                $databaseMod = $databaseMods[$importMod->getName()];

                unset($databaseMods[$importMod->getName()]);
            } else {
                $modsToProcess[] = $importMod->getName();
            }
        }


        return array_keys($this->exportDataService->getMods());
    }
}