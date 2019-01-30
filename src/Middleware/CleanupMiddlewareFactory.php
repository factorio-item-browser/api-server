<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
use FactorioItemBrowser\Api\Server\Database\Service\CleanableServiceInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the cleanup middleware.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CleanupMiddlewareFactory implements FactoryInterface
{
    /**
     * The aliases of the services which may be cleaned.
     */
    protected const CLEANABLE_SERVICES = [
        CachedSearchResultService::class,
    ];

    /**
     * Creates the cleanup middleware.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return CleanupMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new CleanupMiddleware($this->getCleanableServices($container));
    }

    /**
     * Returns the serbice instances to be cleaned.
     * @param ContainerInterface $container
     * @return array|CleanableServiceInterface[]
     */
    protected function getCleanableServices(ContainerInterface $container): array
    {
        $result = [];
        foreach (self::CLEANABLE_SERVICES as $alias) {
            $result[] = $container->get($alias);
        }
        return $result;
    }
}
