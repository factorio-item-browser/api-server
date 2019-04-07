<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Exception;
use FactorioItemBrowser\Api\Client\Request\RequestInterface;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\MalformedRequestException;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware deserializing the request into a client request entity.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RequestDeserializerMiddleware implements MiddlewareInterface
{
    use MatchedRouteNameTrait;

    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The map of the routes to their corresponding requests.
     * @var array|string[]
     */
    protected $mapRouteToRequest;

    /**
     * Initializes the middleware.
     * @param SerializerInterface $serializer
     * @param array|string[] $mapRouteToRequest
     */
    public function __construct(SerializerInterface $serializer, array $mapRouteToRequest)
    {
        $this->serializer = $serializer;
        $this->mapRouteToRequest = $mapRouteToRequest;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ApiServerException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeName = $this->getMatchedRouteName($request);
        $requestClass = $this->mapRouteToRequest[$routeName] ?? '';

        if ($requestClass !== '') {
            $clientRequest = $this->deserializeRequestBody($request, $requestClass);
            $request = $request->withAttribute(RequestInterface::class, $clientRequest);
        }

        return $handler->handle($request);
    }

    /**
     * Deserializes the request body into a client request entity.
     * @param ServerRequestInterface $request
     * @param string $requestClass
     * @return RequestInterface
     * @throws ApiServerException
     */
    protected function deserializeRequestBody(ServerRequestInterface $request, string $requestClass): RequestInterface
    {
        try {
            $requestBody = $request->getBody()->getContents();
            if ($requestBody === '') {
                $requestBody = '{}';
            }
            $result = $this->serializer->deserialize($requestBody, $requestClass, 'json');
        } catch (Exception $e) {
            throw new MalformedRequestException($e->getMessage(), $e);
        }
        return $result;
    }
}
