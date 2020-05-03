<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware injecting the CORS header into the response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CorsHeaderMiddleware implements MiddlewareInterface
{
    /**
     * The headers which are allowed in the requests.
     */
    protected const ALLOWED_HEADERS = [
        'Accept',
        'Accept-Language',
        'Authorization',
        'Content-Language',
        'Content-Type',
    ];

    /**
     * The allowed origins to access the Portal API server.
     * @var array<string>
     */
    protected $allowedOrigins;

    /**
     * Initializes the middleware.
     * @param array<string> $allowedOrigins
     */
    public function __construct(array $allowedOrigins)
    {
        $this->allowedOrigins = $allowedOrigins;
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

        $origin = $request->getServerParams()['HTTP_ORIGIN'] ?? '';
        if ($this->isOriginAllowed($origin)) {
            $response = $this->addHeaders($response, $origin);
        }

        return $response;
    }

    /**
     * Returns whether the origin is allowed.
     * @param string $origin
     * @return bool
     */
    protected function isOriginAllowed(string $origin): bool
    {
        foreach ($this->allowedOrigins as $allowedOrigin) {
            if (preg_match($allowedOrigin, $origin) === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds the needed headers to the response.
     * @param ResponseInterface $response
     * @param string $origin
     * @return ResponseInterface
     */
    protected function addHeaders(ResponseInterface $response, string $origin): ResponseInterface
    {
        $response = $response->withHeader('Access-Control-Allow-Headers', implode(',', self::ALLOWED_HEADERS))
                             ->withHeader('Access-Control-Allow-Origin', $origin);

        if ($response->hasHeader('Allow')) {
            $response = $response->withHeader('Access-Control-Allow-Methods', $response->getHeaderLine('Allow'));
        }

        return $response;
    }
}
