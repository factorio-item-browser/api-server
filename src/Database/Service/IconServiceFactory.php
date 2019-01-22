<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Database\Repository\IconFileRepository;
use FactorioItemBrowser\Api\Database\Repository\IconRepository;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the icon service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconServiceFactory implements FactoryInterface
{
    /**
     * Creates the service instance.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return IconService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var IconFileRepository $iconFileRepository */
        $iconFileRepository = $container->get(IconFileRepository::class);
        /* @var IconRepository $iconRepository */
        $iconRepository = $container->get(IconRepository::class);
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);

        return new IconService($iconFileRepository, $iconRepository, $modService);
    }
}
