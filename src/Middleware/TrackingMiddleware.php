<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Constant\RequestAttributeName;
use FactorioItemBrowser\Api\Server\Service\TrackingService;
use FactorioItemBrowser\Api\Server\Tracking\Event\RequestEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TrackingMiddleware implements MiddlewareInterface
{
    private TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        $trackingRequestEvent = new RequestEvent();

        $request = $request->withAttribute(RequestAttributeName::TRACKING_REQUEST_EVENT, $trackingRequestEvent);
        $response = $handler->handle($request);

        $trackingRequestEvent->runtime = round((microtime(true) - $startTime) * 1000);
        $trackingRequestEvent->statusCode = $response->getStatusCode();

        $this->trackingService->addEvent($trackingRequestEvent);
        $this->trackingService->track();
        return $response;
    }
}
