<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the importer manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ImporterManagerFactory implements FactoryInterface
{
    /**
     * The importer classes to use.
     */
    const IMPORTER_CLASSES = [
        ModImporter::class,
        CombinationImporter::class,
        CraftingCategoryImporter::class,
        ItemImporter::class,
        RecipeImporter::class,
        MachineImporter::class,
        TranslationImporter::class,
        IconImporter::class,
        OrderImporter::class,
    ];

    /**
     * Creates the importer manager.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ImporterManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);

        $importers = [];
        foreach (self::IMPORTER_CLASSES as $parserClass) {
            $importers[] = $container->get($parserClass);
        }

        return new ImporterManager($entityManager, $importers);
    }
}