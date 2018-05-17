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
}