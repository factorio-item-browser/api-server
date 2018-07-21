<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Import;

use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Import\ImporterManager;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the import handler.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ImportHandlerFactory implements FactoryInterface
{
    /**
     * Creates the item random handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ImportHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ItemService $itemService */
        $itemService = $container->get(ItemService::class);
        $itemService->removeOrphans();

        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);
        /* @var ImporterManager $importerManager */
        $importerManager = $container->get(ImporterManager::class);

        return new ImportHandler($exportDataService, $modService, $importerManager);
    }
}
