<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Import;

use BluePsyduck\Common\Data\DataContainer;
use Exception;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Import\ImporterManager;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

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
    ) {
        $this->exportDataService = $exportDataService;
        $this->modService = $modService;
        $this->importerManager = $importerManager;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$request->getAttribute('allowImport')) {
            throw new ApiServerException('API endpoint not found: ' . $request->getRequestTarget(), 404);
        }
        return parent::handle($request);
    }

    /**
     * Creates the input filter to use to verify the request.
     * @return InputFilter
     */
    protected function createInputFilter(): InputFilter
    {
        $inputFilter = new InputFilter();
        $inputFilter
            ->add([
                'name' => 'modName',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ]);
        return $inputFilter;
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        ini_set('max_execution_time', '0');
        $this->modService->setEnabledModCombinationIds([]);

        try {
            $modName = $requestData->getString('modName');
            $databaseMods = $this->modService->getAllMods();
            $exportMod = $this->exportDataService->getMod($modName);
            if (!$exportMod instanceof ExportMod) {
                throw new ApiServerException('Unknown mod to import: ' . $modName);
            }
            $this->importMod($exportMod, $databaseMods[$exportMod->getName()] ?? null);
            $this->importerManager->clean();
        } catch (Exception $e) {
            // Set exception code to 500 to have the exception message printed into the response.
            throw new ApiServerException($e->getMessage(), 500, $e);
        }

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

        if (!$this->hasCombinationWithoutOptionalMods($exportMod)) {
            // We always need the combination with no optional mods loaded because mod meta data are assigned to it.
            $emptyCombination = new ExportCombination();
            $emptyCombination->setName($exportMod->getName())
                             ->setMainModName($exportMod->getName())
                             ->setIsDataLoaded(true); // Do not try to load non-existing combination.

            $this->importCombination(
                $emptyCombination,
                $databaseMod,
                $databaseCombinations[$exportMod->getName()] ?? null
            );
        }

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
     * Checks whether the mod has a combination with no optional mods loaded.
     * @param ExportMod $exportMod
     * @return bool
     */
    protected function hasCombinationWithoutOptionalMods(ExportMod $exportMod): bool
    {
        $result = false;
        foreach ($exportMod->getCombinations() as $exportCombination) {
            if (count($exportCombination->getLoadedOptionalModNames()) === 0) {
                $result = true;
                break;
            }
        }
        return $result;
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
    ) {
        if (!$databaseCombination instanceof DatabaseCombination) {
            $databaseCombination = new DatabaseCombination($databaseMod);
            $databaseCombination->setName($exportCombination->getName());
        }

        if (!$exportCombination->getIsDataLoaded()) {
            $this->exportDataService->loadCombinationData($exportCombination);
        }
        $this->importerManager->importCombination($exportCombination, $databaseCombination);
        return $this;
    }
}
