<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the meta middleware.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MetaMiddlewareFactory implements FactoryInterface
{
    /**
     * Creates the meta middleware.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return MetaMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        return new MetaMiddleware($config['factorio-item-browser']['api-server']['version']);
    }
}