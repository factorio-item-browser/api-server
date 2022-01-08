<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
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
    protected const MAX_AGE = 3600;

    /**
     * The headers which are allowed in the requests.
     */
    protected const ALLOWED_HEADERS = [
        'Accept',
        'Accept-Language',
        'Api-Key',
        'Content-Language',
        'Content-Type',
    ];

    /**
     * Initializes the middleware.
     * @param array<string> $allowedOrigins
     */
    public function __construct(
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::ALLOWED_ORIGINS)]
        protected readonly array $allowedOrigins,
    ) {
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $response = $response->withHeader('Access-Control-Max-Age', (string) self::MAX_AGE);

        $origin = $request->getServerParams()['HTTP_ORIGIN'] ?? '';
        if ($this->isOriginAllowed($origin)) {
            $response = $this->addHeaders($response, $origin);
        }

        return $response;
    }

    /**
     * Returns whether the origin is allowed.
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
