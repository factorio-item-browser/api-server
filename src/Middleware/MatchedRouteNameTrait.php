<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\InternalServerException;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The trait helping with fetching the matched route name from the request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
trait MatchedRouteNameTrait
{
    /**
     * Returns the matched route name from the request.
     * @param ServerRequestInterface $request
     * @return string
     * @throws ApiServerException
     */
    protected function getMatchedRouteName(ServerRequestInterface $request): string
    {
        $routeResult = $request->getAttribute(RouteResult::class);
        if (!$routeResult instanceof RouteResult) {
            throw new InternalServerException('Missing RouteResult in request.');
        }

        return (string) $routeResult->getMatchedRouteName();
    }
}
