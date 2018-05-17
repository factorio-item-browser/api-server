<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
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

            if (count($this->modService->getEnabledModCombinationIds()) > 0) {
                $machineData = $this->filterData($machineData, ['name']);
            }
            $machineIds = [];
            foreach ($machineData as $data) {
                $machineIds[] = intval($data['id']);
            }
            $result = $this->machineRepository->findByIds($machineIds);
        }
        return $result;
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