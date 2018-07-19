<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use Interop\Container\ContainerInterface;

/**
 * The factory of the combination importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationImporterFactory
{
    /**
     * Creates the combination importer.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return CombinationImporter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);

        return new CombinationImporter($entityManager, $modService);
    }
}
