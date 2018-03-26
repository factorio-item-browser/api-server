<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Blast\BaseUrl\BasePathHelper;
use Interop\Container\ContainerInterface;

/**
 * The factory of the documentation redirect middleware.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DocumentationRedirectMiddlewareFactory
{
    /**
     * Creates the database configuration middleware.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return DocumentationRedirectMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var BasePathHelper $basePathHelper */
        $basePathHelper = $container->get(BasePathHelper::class);

        return new DocumentationRedirectMiddleware($basePathHelper);
    }
}