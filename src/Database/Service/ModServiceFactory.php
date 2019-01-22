<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Database\Repository\ModCombinationRepository;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the mod service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModServiceFactory implements FactoryInterface
{
    /**
     * Creates the service instance.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ModService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModRepository $modRepository */
        $modRepository = $container->get(ModRepository::class);
        /* @var ModCombinationRepository $modCombinationRepository */
        $modCombinationRepository = $container->get(ModCombinationRepository::class);

        return new ModService($modRepository, $modCombinationRepository);
    }
}
