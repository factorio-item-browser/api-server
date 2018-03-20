<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * The factory of the database configuration middleware.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DatabaseConfigurationMiddlewareFactory
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
        $config = $container->get('config');
        $configurationAliases = $config['factorio-item-browser']['api-server']['databaseConnection']['aliases'];

        /* @var ServiceManager $container */
        return new DatabaseConfigurationMiddleware($container, $configurationAliases);
    }
}