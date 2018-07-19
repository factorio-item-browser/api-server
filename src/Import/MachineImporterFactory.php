<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Service\CraftingCategoryService;
use FactorioItemBrowser\Api\Server\Database\Service\MachineService;
use Interop\Container\ContainerInterface;

/**
 * The factory of the machine importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineImporterFactory
{
    /**
     * Creates the machine importer.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return MachineImporter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        /* @var CraftingCategoryService $craftingCategoryService */
        $craftingCategoryService = $container->get(CraftingCategoryService::class);
        /* @var MachineService $machineService */
        $machineService = $container->get(MachineService::class);

        return new MachineImporter($entityManager, $craftingCategoryService, $machineService);
    }
}
