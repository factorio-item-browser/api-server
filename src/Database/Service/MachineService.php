<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Data\MachineData;
use FactorioItemBrowser\Api\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;

/**
 * The service class of the machine database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineService extends AbstractModsAwareService
{
    /**
     * The repository of the machines.
     * @var MachineRepository
     */
    protected $machineRepository;

    /**
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->machineRepository = $entityManager->getRepository(Machine::class);
        return $this;
    }

    /**
     * Returns the machines supporting the specified crafting category.
     * @param CraftingCategory $craftingCategory
     * @return array|Machine[]
     */
    public function getByCraftingCategory(CraftingCategory $craftingCategory): array
    {
        $machineData = $this->machineRepository->findDataByCraftingCategories(
            [$craftingCategory->getName()],
            $this->modService->getEnabledModCombinationIds()
        );

        return $this->getDetailsByMachineData($machineData);
    }

    /**
     * Returns the actual machine details from the specified machine data.
     * @param array|MachineData[] $machineData
     * @return array|Machine[]
     */
    protected function getDetailsByMachineData(array $machineData): array
    {
        if (count($this->modService->getEnabledModCombinationIds()) > 0) {
            $machineData = $this->filterData($machineData);
        }

        $machineIds = [];
        foreach ($machineData as $data) {
            if ($data instanceof MachineData) {
                $machineIds[] = $data->getId();
            }
        }
        return $this->machineRepository->findByIds($machineIds);
    }

    /**
     * Filters the specified machine names to only include the actually available ones.
     * @param array|string[] $names
     * @return array|string[]
     */
    public function filterAvailableNames(array $names): array
    {
        $recipeData = $this->machineRepository->findDataByNames(
            $names,
            $this->modService->getEnabledModCombinationIds()
        );

        $result = [];
        foreach ($recipeData as $data) {
            $result[$data->getName()] = true;
        }
        return array_keys($result);
    }
}
