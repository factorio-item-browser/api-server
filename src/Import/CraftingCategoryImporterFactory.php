<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Service\CraftingCategoryService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the craftingCategory importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CraftingCategoryImporterFactory implements FactoryInterface
{
    /**
     * Creates the crafting category importer.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return CraftingCategoryImporter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        /* @var CraftingCategoryService $craftingCategoryService */
        $craftingCategoryService = $container->get(CraftingCategoryService::class);

        return new CraftingCategoryImporter($entityManager, $craftingCategoryService);
    }
}