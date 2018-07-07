<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the mod meta handler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModMetaHandlerFactory implements FactoryInterface
{
    /**
     * Creates the mod meta handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ModMetaHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);

        return new ModMetaHandler($modService);
    }
}
