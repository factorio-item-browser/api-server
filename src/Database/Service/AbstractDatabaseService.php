<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;

/**
 * The abstract class of the database services.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractDatabaseService
{
    /**
     * The doctrine entity manager.
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Initializes the service.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->initializeRepositories($entityManager);
    }

    /**
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    abstract protected function initializeRepositories(EntityManager $entityManager);
}