<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
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
    private readonly string $version;
    private readonly float $startTime;

    public function __construct(
        #[ReadConfig('version')]
        string $version,
    ) {
        $this->version = $version;
        $this->startTime = microtime(true);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $response->withHeader('Version', $this->version)
                        ->withHeader('Runtime', (string) (round(microtime(true) - $this->startTime, 3)));
    }
}
