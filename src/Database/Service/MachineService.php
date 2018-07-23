<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Server\Database\Entity\Machine;
use FactorioItemBrowser\Api\Server\Database\Repository\MachineRepository;

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
     * Returns the machines with the specified names.
     * @param array|string[] $names
     * @return array|Machine[]
     */
    public function getByNames(array $names): array
    {
        $result = [];
        if (count($names) > 0) {
            $machineData = $this->machineRepository->findIdDataByNames(
                $names,
                $this->modService->getEnabledModCombinationIds()
            );
            $result = $this->getDetailsByMachineData($machineData);
        }
        return $result;
    }

    /**
     * Returns the machines supporting the specified crafting category.
     * @param CraftingCategory $craftingCategory
     * @return array|Machine[]
     */
    public function getByCraftingCategory(CraftingCategory $craftingCategory): array
    {
        $machineData = $this->machineRepository->findIdDataByCraftingCategories(
            [$craftingCategory->getName()],
            $this->modService->getEnabledModCombinationIds()
        );
        return $this->getDetailsByMachineData($machineData);
    }

    /**
     * Returns the actual machine details from the specified machine data.
     * @param array $machineData
     * @return array|Machine[]
     */
    protected function getDetailsByMachineData(array $machineData): array
    {
        if (count($this->modService->getEnabledModCombinationIds()) > 0) {
            $machineData = $this->filterData($machineData, ['name']);
        }
        $machineIds = [];
        foreach ($machineData as $data) {
            $machineIds[] = intval($data['id']);
        }

        $result = [];
        if (count($machineIds) > 0) {
            $result = $this->machineRepository->findByIds($machineIds);
        }
        return $result;
    }

    /**
     * Filters the specified machine names to only include the actually available ones.
     * @param array|string[] $names
     * @return array|string[]
     */
    public function filterAvailableNames(array $names): array
    {
        $result = [];
        if (count($names) > 0) {
            $recipeData = $this->machineRepository->findIdDataByNames(
                $names,
                $this->modService->getEnabledModCombinationIds()
            );
            foreach ($recipeData as $data) {
                $result[$data['name']] = true;
            }
        }
        return array_map('strval', array_keys($result));
    }

    /**
     * Removes any orphaned machines, i.e. machines no longer used by any combination.
     * @return $this
     */
    public function removeOrphans()
    {
        $this->machineRepository->removeOrphans();
        return $this;
    }
}
