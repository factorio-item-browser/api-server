<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Import;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;

/**
 * The factory of the order importer.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class OrderImporterFactory
{
    /**
     * Creates the order importer.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return OrderImporter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);
        /* @var ExportDataService $exportDataService */
        $exportDataService = $container->get(ExportDataService::class);

        return new OrderImporter($modService, $exportDataService);
    }
}