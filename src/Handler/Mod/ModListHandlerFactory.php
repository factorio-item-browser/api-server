<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Mod;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the mod list handler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModListHandlerFactory implements FactoryInterface
{
    /**
     * Creates the mod list handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ModListHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);
        /* @var TranslationService $translationService */
        $translationService = $container->get(TranslationService::class);

        return new ModListHandler($modService, $translationService);
    }
}