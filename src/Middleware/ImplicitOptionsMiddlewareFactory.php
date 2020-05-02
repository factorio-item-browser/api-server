<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Interop\Container\ContainerInterface;
use Laminas\Diactoros\Response;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Psr\Http\Message\ResponseInterface;

/**
 * The factory of the ImplicitOptionsMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ImplicitOptionsMiddlewareFactory implements FactoryInterface
{
    /**
     * The max age of the option responses.
     */
    protected const MAX_AGE = 3600;

    /**
     * Creates the middleware
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return ImplicitOptionsMiddleware
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): ImplicitOptionsMiddleware {
        return new ImplicitOptionsMiddleware(function (): ResponseInterface {
            $response = new Response();
            $response = $response->withHeader('Access-Control-Max-Age', (string) self::MAX_AGE);
            return $response;
        });
    }
}
