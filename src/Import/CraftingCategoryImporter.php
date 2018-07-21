<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Service\CraftingCategoryService;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;

/**
 * The class importing the crafting categories.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CraftingCategoryImporter implements ImporterInterface
{
    /**
     * The Doctrine entity manager.
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The database service of the crafting categories.
     * @var CraftingCategoryService
     */
    protected $craftingCategoryService;

    /**
     * Initializes the importer.
     * @param EntityManager $entityManager
     * @param CraftingCategoryService $craftingCategoryService
     */
    public function __construct(EntityManager $entityManager, CraftingCategoryService $craftingCategoryService)
    {
        $this->entityManager = $entityManager;
        $this->craftingCategoryService = $craftingCategoryService;
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
        $craftingCategoryNames = $this->findCraftingCategories($exportCombination);
        $craftingCategories = $this->craftingCategoryService->getByNames($craftingCategoryNames);
        foreach ($craftingCategoryNames as $craftingCategoryName) {
            if (!isset($craftingCategories[$craftingCategoryName])) {
                $craftingCategory = new CraftingCategory($craftingCategoryName);
                $craftingCategories[$craftingCategoryName] = $craftingCategory;
                $this->entityManager->persist($craftingCategory);
            }
        }
        return $this;
    }

    /**
     * Finds the crafting categories used in the export.
     * @param ExportCombination $exportCombination
     * @return array|string
     */
    protected function findCraftingCategories(ExportCombination $exportCombination): array
    {
        $result = [];
        foreach ($exportCombination->getData()->getRecipes() as $recipe) {
            $result[$recipe->getCraftingCategory()] = true;
        }
        foreach ($exportCombination->getData()->getMachines() as $machine) {
            foreach ($machine->getCraftingCategories() as $craftingCategory) {
                $result[$craftingCategory] = true;
            }
        }
        return array_keys($result);
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
