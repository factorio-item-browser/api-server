<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use Exception;
use FactorioItemBrowser\Api\Client\Constant\ServiceName;
use FactorioItemBrowser\Api\Client\Request\AbstractRequest;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\Constant\RequestAttributeName;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Exception\InvalidRequestBodyException;
use FactorioItemBrowser\Api\Server\Tracking\Event\RequestEvent;
use FactorioItemBrowser\Common\Constant\Defaults;
use JMS\Serializer\SerializerInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware deserializing the request body if required.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RequestDeserializerMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string, class-string<AbstractRequest>> $requestClassesByRoutes
     */
    public function __construct(
        #[Alias(ServiceName::SERIALIZER)]
        private readonly SerializerInterface $serializer,
        #[ReadConfig(ConfigKey::MAIN, ConfigKey::REQUEST_CLASSES_BY_ROUTES)]
        private readonly array $requestClassesByRoutes,
    ) {
    }

    /**
     * @throws ServerException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $requestClass = $this->requestClassesByRoutes[$routeResult->getMatchedRouteName()];
        $locale = $request->getHeaderLine('Accept-Language');
        $combinationId = strval($request->getAttribute('combination-id'));

        /** @var RequestEvent $trackingRequestEvent */
        $trackingRequestEvent = $request->getAttribute(RequestAttributeName::TRACKING_REQUEST_EVENT);
        $trackingRequestEvent->routeName = (string) $routeResult->getMatchedRouteName();
        $trackingRequestEvent->combinationId = $combinationId;
        $trackingRequestEvent->locale = $locale;

        try {
            if ($request->getHeaderLine('Content-Type') === 'application/json') {
                $requestBody = $request->getBody()->getContents();
            } else {
                $requestBody = '{}';
            }

            /** @var AbstractRequest $clientRequest */
            $clientRequest = $this->serializer->deserialize($requestBody, $requestClass, 'json');
        } catch (Exception $e) {
            throw new InvalidRequestBodyException($e->getMessage(), $e);
        }

        $clientRequest->locale = $locale === '' ? Defaults::LOCALE : $locale;
        $clientRequest->combinationId =  $combinationId;

        $request = $request->withParsedBody($clientRequest);
        return $handler->handle($request);
    }
}
