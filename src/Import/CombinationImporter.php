<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;

/**
 * The class importing the combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationImporter implements ImporterInterface
{
    /**
     * The Doctrine entity manager.
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The database service of the mods.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the importer.
     * @param EntityManager $entityManager
     * @param ModService $modService
     */
    public function __construct(EntityManager $entityManager, ModService $modService)
    {
        $this->entityManager = $entityManager;
        $this->modService = $modService;
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
        $databaseCombination->setOptionalModIds($this->getIdsOfMods($exportCombination->getLoadedOptionalModNames()));

        $this->entityManager->persist($databaseCombination);
        return $this;
    }

    /**
     * Returns the ids of the specified mods.
     * @param array|string[] $modNames
     * @return array|int[]
     * @throws ApiServerException
     */
    protected function getIdsOfMods(array $modNames): array
    {
        $mods = $this->modService->getAllMods();
        $result = [];
        foreach ($modNames as $modName) {
            if (!isset($mods[$modName])) {
                throw new ApiServerException('Optionally loaded mod ' . $modName . ' does not exist in the database.');
            }
            $result[] = $mods[$modName]->getId();
        }
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
