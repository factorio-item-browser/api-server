<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Exception;
use FactorioItemBrowser\Api\Client\Request\AbstractRequest;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\InvalidRequestBodyException;
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
    private SerializerInterface $combinationApiClientSerializer;

    /** @var array<string, class-string<AbstractRequest>> */
    private array $requestClassesByRoutes;

    /**
     * @param SerializerInterface $apiClientSerializer
     * @param array<string, class-string<AbstractRequest>> $requestClassesByRoutes
     */
    public function __construct(SerializerInterface $apiClientSerializer, array $requestClassesByRoutes)
    {
        $this->combinationApiClientSerializer = $apiClientSerializer;
        $this->requestClassesByRoutes = $requestClassesByRoutes;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ApiServerException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $requestClass = $this->requestClassesByRoutes[$routeResult->getMatchedRouteName()] ?? '';
        $locale = $request->getHeaderLine('Accept-Language');

        if ($request->getHeaderLine('Content-Type') !== 'application/json') {
            throw new InvalidRequestBodyException('Missing or invalid Content-Type.');
        }

        if ($requestClass !== '') {
            try {
                /** @var AbstractRequest $clientRequest */
                $clientRequest = $this->combinationApiClientSerializer->deserialize(
                    $request->getBody()->getContents(),
                    $requestClass,
                    'json',
                );
                $clientRequest->locale = $locale === '' ? Defaults::LOCALE : $locale;
                $clientRequest->combinationId =  $request->getAttribute('combination-id');

                $request = $request->withParsedBody($clientRequest);
            } catch (Exception $e) {
                throw new InvalidRequestBodyException($e->getMessage(), $e);
            }
        }

        return $handler->handle($request);
    }
}
