<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the auth handler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthHandlerFactory implements FactoryInterface
{
    /**
     * Creates the auth handler.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AuthHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $authorizationConfig = $config['factorio-item-browser']['api-server']['authorization'];

        /* @var ModService $modService */
        $modService = $container->get(ModService::class);

        return new AuthHandler($authorizationConfig['key'], $authorizationConfig['agents'], $modService);
    }
}