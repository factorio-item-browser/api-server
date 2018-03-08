<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
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
    /**
     * The factor to decide whether to actually run the cleanup.
     */
    const CLEANUP_FACTOR = 1000;

    /**
     * The database cached search result service.
     * @var CachedSearchResultService
     */
    protected $cachedSearchResultService;

    /**
     * Initializes the middleware.
     * @param CachedSearchResultService $cachedSearchResultService
     */
    public function __construct(CachedSearchResultService $cachedSearchResultService)
    {
        $this->cachedSearchResultService = $cachedSearchResultService;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (mt_rand(0, self::CLEANUP_FACTOR) === 42) {
            $this->cachedSearchResultService->cleanup();
        }
        return $response;
    }
}