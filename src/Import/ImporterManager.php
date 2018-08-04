<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;

/**
 * The manager of the importer classes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ImporterManager
{
    /**
     * The entity manager.
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The importers.
     * @var array|ImporterInterface[]
     */
    protected $importers;

    /**
     * Initializes the importer manager.
     * @param EntityManager $entityManager
     * @param array|ImporterInterface[] $importers
     */
    public function __construct(EntityManager $entityManager, array $importers)
    {
        $this->entityManager = $entityManager;
        $this->importers = $importers;
    }

    /**
     * Imports the mod.
     * @param ExportMod $exportMod
     * @param DatabaseMod $databaseMod
     * @return $this
     */
    public function importMod(ExportMod $exportMod, DatabaseMod $databaseMod)
    {
        foreach ($this->importers as $importer) {
            $importer->importMod($exportMod, $databaseMod);
            $this->entityManager->flush();
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
        foreach ($this->importers as $importer) {
            $importer->importCombination($exportCombination, $databaseCombination);
            $this->entityManager->flush();
        }
        return $this;
    }

    /**
     * Cleans up any no longer needed data.
     * @return $this
     */
    public function clean()
    {
        foreach (array_reverse($this->importers) as $importer) {
            /* @var ImporterInterface $importer */
            $importer->clean();
            $this->entityManager->flush();
        }
        return $this;
    }
}
