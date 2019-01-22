<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the machine service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineServiceFactory implements FactoryInterface
{
    /**
     * Creates the service instance.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return MachineService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var MachineRepository $machineRepository */
        $machineRepository = $container->get(MachineRepository::class);
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);

        return new MachineService($machineRepository, $modService);
    }
}
