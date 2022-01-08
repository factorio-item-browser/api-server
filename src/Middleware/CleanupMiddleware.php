<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Search\SearchCacheClearInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware occasionally cleaning up not-needed data.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CleanupMiddleware implements MiddlewareInterface
{
    private const CLEANUP_FACTOR = 1000;

    public function __construct(
        private readonly SearchCacheClearInterface $searchCacheClearer,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (mt_rand(0, self::CLEANUP_FACTOR) === 42) {
            $this->searchCacheClearer->clearExpiredResults();
        }

        return $response;
    }
}
