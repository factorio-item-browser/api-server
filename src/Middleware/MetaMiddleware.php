<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware adding the meta node to the response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MetaMiddleware implements MiddlewareInterface
{
    /**
     * The version of the API currently in use.
     * @var string
     */
    protected $version;

    /**
     * The start time of the execution.
     * @var float
     */
    protected $startTime;

    /**
     * Initializes the meta middleware.
     * @param string $version
     */
    public function __construct(string $version)
    {
        $this->version = $version;
        $this->startTime = microtime(true);
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
        return $response->withHeader('X-Version', $this->version)
                        ->withHeader('X-Runtime', round(microtime(true) - $this->startTime, 3));
    }
}
