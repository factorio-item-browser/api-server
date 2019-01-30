<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\CleanableServiceInterface;
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
    protected const CLEANUP_FACTOR = 1000;

    /**
     * The services which may be cleaned.
     * @var array|CleanableServiceInterface[]
     */
    protected $cleanableServices;

    /**
     * Initializes the middleware.
     * @param array|CleanableServiceInterface[] $cleanableServices
     */
    public function __construct(array $cleanableServices)
    {
        $this->cleanableServices = $cleanableServices;
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

        foreach ($this->cleanableServices as $cleanableService) {
            if ($this->getRandomNumber(self::CLEANUP_FACTOR) === 42) {
                $cleanableService->cleanup();
            }
        }

        return $response;
    }

    /**
     * Returns a random number with the specified factor.
     * @param int $factor
     * @return int
     */
    protected function getRandomNumber($factor): int
    {
        return mt_rand(0, $factor);
    }
}
