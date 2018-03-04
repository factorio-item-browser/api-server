<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the authorization middleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthorizationMiddlewareFactory implements FactoryInterface
{
    /**
     * Creates the authorization middleware.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return AuthorizationMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $authorizationConfig = $config['factorio-item-browser']['api-server']['authorization'];

        /* @var ModService $modService */
        $modService = $container->get(ModService::class);

        return new AuthorizationMiddleware($authorizationConfig['key'], $modService);
    }
}