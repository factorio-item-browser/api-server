<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\ExportData;

use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the export data service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportDataServiceFactory implements FactoryInterface
{
    /**
     * Creates the export data service.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ExportDataService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        $service = new ExportDataService($config['factorio-item-browser']['export-data']['directory']);
        $service->loadMods();
        return $service;
    }
}
