<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Client\Constant\RecipeMode;
use FactorioItemBrowser\Api\Server\Database\Entity\Icon as DatabaseIcon;
use FactorioItemBrowser\Api\Server\Database\Entity\IconFile as DatabaseIconFile;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Service\IconService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;
use FactorioItemBrowser\ExportData\Service\ExportDataService;

/**
 * The class importing the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconImporter implements ImporterInterface
{
    /**
     * The Doctrine entity manager.
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The export data service.
     * @var ExportDataService
     */
    protected $exportDataService;

    /**
     * The database service of the icons.
     * @var IconService
     */
    protected $iconService;

    /**
     * The database icon files used in the importer.
     * @var array|DatabaseIconFile[]
     */
    protected $databaseIconFiles = [];

    /**
     * Initializes the importer.
     * @param EntityManager $entityManager
     * @param ExportDataService $exportDataService
     * @param IconService $iconService
     */
    public function __construct(
        EntityManager $entityManager,
        ExportDataService $exportDataService,
        IconService $iconService
    ) {
        $this->entityManager = $entityManager;
        $this->exportDataService = $exportDataService;
        $this->iconService = $iconService;
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
        $this->databaseIconFiles = $this->getExistingIconFiles($exportCombination);
        $this->databaseIconFiles = $this->updateIconFiles($exportCombination, $this->databaseIconFiles);
        $this->assignIconsToCombination($exportCombination, $databaseCombination);
        return $this;
    }

    /**
     * Returns the existing icon files from the database.
     * @param ExportCombination $exportCombination
     * @return array|DatabaseIconFile[]
     */
    protected function getExistingIconFiles(ExportCombination $exportCombination): array
    {
        $hashes = [];
        foreach ($exportCombination->getData()->getIcons() as $exportIcon) {
            $hashes[] = $exportIcon->getHash();
        }

        return $this->iconService->getIconFilesByHashes($hashes);
    }

    /**
     * @param ExportCombination $exportCombination
     * @param array|DatabaseIconFile[] $databaseIconFiles
     * @return array|DatabaseIconFile[]
     */
    protected function updateIconFiles(ExportCombination $exportCombination, array $databaseIconFiles): array
    {
        foreach ($exportCombination->getData()->getIcons() as $exportIcon) {
            $hash = $exportIcon->getHash();
            if (!isset($databaseIconFiles[$hash])) {
                $databaseIconFile = new DatabaseIconFile($hash);
                $databaseIconFiles[$hash] = $databaseIconFile;
                $this->entityManager->persist($databaseIconFile);
            }

            $iconContents = $this->exportDataService->loadIcon($exportIcon->getHash());
            $databaseIconFiles[$hash]->setImage($iconContents);
        }
        return $databaseIconFiles;
    }

    /**
     * Assigns the icons to the database combination.
     * @param ExportCombination $exportCombination
     * @param DatabaseCombination $databaseCombination
     * @return $this
     */
    protected function assignIconsToCombination(
        ExportCombination $exportCombination,
        DatabaseCombination $databaseCombination
    ) {
        /* @var DatabaseIcon[] $combinationIcons */
        $combinationIcons = [];
        foreach ($databaseCombination->getIcons() as $combinationIcon) {
            $key = $combinationIcon->getType() . '|' . $combinationIcon->getName();
            $combinationIcons[$key] = $combinationIcon;
        }

        // Process item icons
        foreach ($exportCombination->getData()->getItems() as $exportItem) {
            if (strlen($exportItem->getIconHash()) > 0) {
                $hash = $exportItem->getIconHash();
                $key = $exportItem->getType() . '|' . $exportItem->getName();
                if (isset($combinationIcons[$key])) {
                    $combinationIcon = $combinationIcons[$key];
                    $combinationIcon->setFile($this->getIconFile($hash));
                    unset($combinationIcons[$key]);
                } else {
                    $combinationIcon = new DatabaseIcon($databaseCombination, $this->getIconFile($hash));
                    $combinationIcon->setType($exportItem->getType())
                                    ->setName($exportItem->getName());
                    $databaseCombination->getIcons()->add($combinationIcon);
                    $this->entityManager->persist($combinationIcon);
                }
            }
        }

        // Process recipe icons
        foreach ($exportCombination->getData()->getRecipes() as $exportRecipe) {
            if (strlen($exportRecipe->getIconHash()) > 0 && $exportRecipe->getMode() === RecipeMode::NORMAL) {
                $hash = $exportRecipe->getIconHash();
                $key = EntityType::RECIPE . '|' . $exportRecipe->getName();
                if (isset($combinationIcons[$key])) {
                    $combinationIcon = $combinationIcons[$key];
                    $combinationIcon->setFile($this->getIconFile($hash));
                    unset($combinationIcons[$key]);
                } else {
                    $combinationIcon = new DatabaseIcon($databaseCombination, $this->getIconFile($hash));
                    $combinationIcon->setType(EntityType::RECIPE)
                                    ->setName($exportRecipe->getName());
                    $databaseCombination->getIcons()->add($combinationIcon);
                    $this->entityManager->persist($combinationIcon);
                }
            }
        }

        // Process machine icons
        foreach ($exportCombination->getData()->getMachines() as $exportMachine) {
            if (strlen($exportMachine->getIconHash()) > 0) {
                $hash = $exportMachine->getIconHash();
                $key = EntityType::MACHINE . '|' . $exportMachine->getName();
                if (isset($combinationIcons[$key])) {
                    $combinationIcon = $combinationIcons[$key];
                    $combinationIcon->setFile($this->getIconFile($hash));
                    unset($combinationIcons[$key]);
                } else {
                    $combinationIcon = new DatabaseIcon($databaseCombination, $this->getIconFile($hash));
                    $combinationIcon->setType(EntityType::MACHINE)
                                    ->setName($exportMachine->getName());
                    $databaseCombination->getIcons()->add($combinationIcon);
                    $this->entityManager->persist($combinationIcon);
                }
            }
        }
        
        foreach ($combinationIcons as $combinationIcon) {
            $databaseCombination->getIcons()->removeElement($combinationIcon);
        }
        return $this;
    }

    /**
     * Returns the icon file with the specified hash, fetching it from the database if not already done.
     * @param string $hash
     * @return DatabaseIconFile
     * @throws ApiServerException
     */
    protected function getIconFile(string $hash): DatabaseIconFile
    {
        if (!isset($this->databaseIconFiles[$hash])) {
            $newIconFiles = $this->iconService->getIconFilesByHashes([$hash]);
            if (!isset($newIconFiles[$hash])) {
                throw new ApiServerException('Unknown icon hash ' . $hash);
            }
            $this->databaseIconFiles[$hash] = $newIconFiles[$hash];
        }
        return $this->databaseIconFiles[$hash];
    }

    /**
     * Cleans up any no longer needed data.
     * @return $this
     */
    public function clean()
    {
        $this->iconService->removeOrphans();
        return $this;
    }
}
