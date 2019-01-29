<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
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
        /* @var AuthorizationService $authorizationService */
        $authorizationService = $container->get(AuthorizationService::class);
        /* @var ModService $modService */
        $modService = $container->get(ModService::class);

        return new AuthorizationMiddleware($authorizationService, $modService);
    }
}
