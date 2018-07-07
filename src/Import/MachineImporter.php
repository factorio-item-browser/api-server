<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\Machine as DatabaseMachine;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination as DatabaseCombination;
use FactorioItemBrowser\Api\Server\Database\Service\CraftingCategoryService;
use FactorioItemBrowser\Api\Server\Database\Service\MachineService;
use FactorioItemBrowser\ExportData\Entity\Machine as ExportMachine;
use FactorioItemBrowser\ExportData\Entity\Mod as ExportMod;
use FactorioItemBrowser\ExportData\Entity\Mod\Combination as ExportCombination;

/**
 * The class importing the machines into the database.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineImporter implements ImporterInterface
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
     * The database service of the machines.
     * @var MachineService
     */
    protected $machineService;

    /**
     * Initializes the importer.
     * @param EntityManager $entityManager
     * @param CraftingCategoryService $craftingCategoryService
     * @param MachineService $machineService
     */
    public function __construct(
        EntityManager $entityManager,
        CraftingCategoryService $craftingCategoryService,
        MachineService $machineService
    ) {
        $this->entityManager = $entityManager;
        $this->craftingCategoryService = $craftingCategoryService;
        $this->machineService = $machineService;
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
        $databaseMachines = $this->getExistingMachines($exportCombination);
        $databaseMachines = $this->addMissingMachines($exportCombination, $databaseMachines);
        $this->assignMachinesToCombination($databaseCombination, $databaseMachines);

        return $this;
    }

    /**
     * Returns the machines already existing in the database.
     * @param ExportCombination $exportCombination
     * @return array
     */
    protected function getExistingMachines(ExportCombination $exportCombination): array
    {
        $machineNames = [];
        foreach ($exportCombination->getData()->getMachines() as $machine) {
            if (count($machine->getCraftingCategories()) > 0) {
                $machineNames[] = $machine->getName();
            }
        }

        $result = [];
        foreach ($this->machineService->getByNames($machineNames) as $databaseMachine) {
            $exportMachine = $exportCombination->getData()->getMachine($databaseMachine->getName());
            if ($exportMachine instanceof ExportMachine
                && $this->hashExportMachine($exportMachine) === $this->hashDatabaseMachine($databaseMachine)
            ) {
                $result[$databaseMachine->getName()] = $databaseMachine;
            }
        }
        return $result;
    }

    /**
     * Hashes the specified machine from the database.
     * @param DatabaseMachine $databaseMachine
     * @return string
     */
    protected function hashDatabaseMachine(DatabaseMachine $databaseMachine): string
    {
        $craftingCategories = [];
        foreach ($databaseMachine->getCraftingCategories() as $craftingCategory) {
            $craftingCategories[] = $craftingCategory->getName();
        }
        sort($craftingCategories);

        $data = [
            'craftingCategories' => array_values($craftingCategories),
            'craftingSpeed' => $databaseMachine->getCraftingSpeed(),
            'numberOfItemSlots' => $databaseMachine->getNumberOfItemSlots(),
            'numberOfFluidInputSlots' => $databaseMachine->getNumberOfFluidInputSlots(),
            'numberOfFluidOutputSlots' => $databaseMachine->getNumberOfFluidOutputSlots(),
            'numberOfModuleSlots' => $databaseMachine->getNumberOfModuleSlots(),
            'energyUsage' => $databaseMachine->getEnergyUsage(),
            'energyUsageUnit' => $databaseMachine->getEnergyUsageUnit()
        ];
        return hash('crc32b', json_encode($data));
    }

    /**
     * Hashes the specified machine of the export.
     * @param ExportMachine $exportMachine
     * @return string
     */
    protected function hashExportMachine(ExportMachine $exportMachine): string
    {
        $craftingCategories = $exportMachine->getCraftingCategories();
        sort($craftingCategories);

        $data = [
            'craftingCategories' => array_values($craftingCategories),
            'craftingSpeed' => $exportMachine->getCraftingSpeed(),
            'numberOfItemSlots' => $exportMachine->getNumberOfItemSlots(),
            'numberOfFluidInputSlots' => $exportMachine->getNumberOfFluidInputSlots(),
            'numberOfFluidOutputSlots' => $exportMachine->getNumberOfFluidOutputSlots(),
            'numberOfModuleSlots' => $exportMachine->getNumberOfModuleSlots(),
            'energyUsage' => $exportMachine->getEnergyUsage(),
            'energyUsageUnit' => $exportMachine->getEnergyUsageUnit()
        ];
        return hash('crc32b', json_encode($data));
    }

    /**
     * Adds missing machines to the database.
     * @param ExportCombination $exportCombination
     * @param array|DatabaseMachine[] $databaseMachines
     * @return array|DatabaseMachine[]
     */
    protected function addMissingMachines(ExportCombination $exportCombination, array $databaseMachines): array
    {
        foreach ($exportCombination->getData()->getMachines() as $exportMachine) {
            if (count($exportMachine->getCraftingCategories()) > 0
                && !$databaseMachines[$exportMachine->getName()] instanceof DatabaseMachine
            ) {
                $databaseMachines[$exportMachine->getName()] = $this->persistMachine($exportMachine);
            }
        }

        return $databaseMachines;
    }

    /**
     * Persists the specified machine into the database.
     * @param ExportMachine $exportMachine
     * @return DatabaseMachine
     */
    protected function persistMachine(ExportMachine $exportMachine): DatabaseMachine
    {
        $databaseMachine = new DatabaseMachine($exportMachine->getName());
        $databaseMachine->setCraftingSpeed($exportMachine->getCraftingSpeed())
                        ->setNumberOfItemSlots($exportMachine->getNumberOfItemSlots())
                        ->setNumberOfFluidInputSlots($exportMachine->getNumberOfFluidInputSlots())
                        ->setNumberOfFluidOutputSlots($exportMachine->getNumberOfFluidOutputSlots())
                        ->setNumberOfModuleSlots($exportMachine->getNumberOfModuleSlots())
                        ->setEnergyUsage($exportMachine->getEnergyUsage())
                        ->setEnergyUsageUnit($exportMachine->getEnergyUsageUnit());

        $craftingCategories = $this->craftingCategoryService->getByNames($exportMachine->getCraftingCategories());
        foreach ($craftingCategories as $craftingCategory) {
            $databaseMachine->getCraftingCategories()->add($craftingCategory);
        }

        $this->entityManager->persist($databaseMachine);
        return $databaseMachine;
    }

    /**
     * Assigns the machines to the database combination.
     * @param DatabaseCombination $databaseCombination
     * @param array|DatabaseMachine[] $databaseMachines
     * @return $this
     */
    protected function assignMachinesToCombination(DatabaseCombination $databaseCombination, array $databaseMachines)
    {
        foreach ($databaseCombination->getMachines() as $combinationMachine) {
            if (isset($databaseMachines[$combinationMachine->getName()])) {
                unset($databaseMachines[$combinationMachine->getName()]);
            } else {
                $databaseCombination->getMachines()->removeElement($combinationMachine);
            }
        }

        foreach ($databaseMachines as $databaseMachine) {
            $databaseCombination->getMachines()->add($databaseMachine);
        }
        return $this;
    }

    /**
     * Cleans up any no longer needed data.
     * @return $this
     */
    public function clean()
    {
        $this->machineService->removeOrphans();
        return $this;
    }
}
