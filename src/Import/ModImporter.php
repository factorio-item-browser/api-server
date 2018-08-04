<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Constant\ModDependencyType;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Database\Entity\ModDependency as DatabaseDependency;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;
use FactorioItemBrowser\ExportData\Entity\Mod\Dependency as ExportDependency;

/**
 * The class importing the actual mod.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModImporter implements ImporterInterface
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
        $databaseMod->setAuthor($exportMod->getAuthor())
                    ->setCurrentVersion($exportMod->getVersion());
        $this->entityManager->persist($databaseMod);

        $this->processDependencies($exportMod, $databaseMod);
        return $this;
    }

    /**
     * Processes the dependencies of the mod.
     * @param ExportMod $exportMod
     * @param DatabaseMod $databaseMod
     * @return $this
     */
    protected function processDependencies(ExportMod $exportMod, DatabaseMod $databaseMod)
    {
        $databaseDependencies = [];
        foreach ($databaseMod->getDependencies() as $databaseDependency) {
            $databaseDependencies[$databaseDependency->getRequiredMod()->getName()] = $databaseDependency;
        }

        foreach ($exportMod->getDependencies() as $exportDependency) {
            $databaseDependency = $databaseDependencies[$exportDependency->getRequiredModName()] ?? null;
            if ($databaseDependency instanceof DatabaseDependency) {
                // Update existing dependency
                $databaseDependency->setRequiredVersion($exportDependency->getRequiredVersion());
                if ($exportDependency->getIsMandatory()) {
                    $databaseDependency->setType(ModDependencyType::MANDATORY);
                } else {
                    $databaseDependency->setType(ModDependencyType::OPTIONAL);
                }
                unset($databaseDependencies[$exportDependency->getRequiredModName()]);
            } else {
                // Add new dependency
                $databaseDependency = $this->convertDependency($exportDependency, $databaseMod);
                if ($databaseDependency instanceof DatabaseDependency) {
                    $databaseMod->getDependencies()->add($databaseDependency);
                    $this->entityManager->persist($databaseDependency);
                }
            }
        }

        foreach ($databaseDependencies as $databaseDependency) {
            $databaseMod->getDependencies()->removeElement($databaseDependency);
            $this->entityManager->remove($databaseDependency);
        }
        return $this;
    }

    /**
     * Converts the specified export dependency to a database one.
     * @param ExportDependency $exportDependency
     * @param DatabaseMod $databaseMod
     * @return DatabaseDependency|null
     */
    protected function convertDependency(
        ExportDependency $exportDependency,
        DatabaseMod $databaseMod
    ): ?DatabaseDependency {
        $databaseDependency = null;
        $requiredMods = $this->modService->getModsWithDependencies([$exportDependency->getRequiredModName()]);
        if (isset($requiredMods[$exportDependency->getRequiredModName()])) {
            $databaseDependency = new DatabaseDependency(
                $databaseMod,
                $requiredMods[$exportDependency->getRequiredModName()]
            );
            $databaseDependency->setRequiredVersion($exportDependency->getRequiredVersion());
            if ($exportDependency->getIsMandatory()) {
                $databaseDependency->setType(ModDependencyType::MANDATORY);
            } else {
                $databaseDependency->setType(ModDependencyType::OPTIONAL);
            }
        } elseif ($exportDependency->getIsMandatory()) {
            throw new ApiServerException(
                'Missing mandatory dependency in database: ' . $exportDependency->getRequiredModName()
            );
        }
        return $databaseDependency;
    }

    /**
     * Imports the combination.
     * @param ExportCombination $exportCombination
     * @param DatabaseCombination $databaseCombination
     * @return $this
     */
    public function importCombination(ExportCombination $exportCombination, DatabaseCombination $databaseCombination)
    {
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
