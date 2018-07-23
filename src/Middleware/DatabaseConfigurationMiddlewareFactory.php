<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * The factory of the database configuration middleware.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DatabaseConfigurationMiddlewareFactory implements FactoryInterface
{
    /**
     * Creates the database configuration middleware.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return DatabaseConfigurationMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (!$container instanceof ServiceManager) {
            throw new ServiceNotCreatedException('Service can only be created using the Zend ServiceManager.');
        }

        $config = $container->get('config');
        $configurationAliases = $config['factorio-item-browser']['api-server']['databaseConnection']['aliases'];

        return new DatabaseConfigurationMiddleware($container, $configurationAliases);
    }
}
