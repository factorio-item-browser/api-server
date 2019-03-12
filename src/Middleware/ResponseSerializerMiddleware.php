<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;

/**
 * The middleware serializing the response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ResponseSerializerMiddleware implements MiddlewareInterface
{
    /**
     * The serializer.
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * The translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the middleware.
     * @param SerializerInterface $serializer
     * @param TranslationService $translationService
     */
    public function __construct(SerializerInterface $serializer, TranslationService $translationService)
    {
        $this->serializer = $serializer;
        $this->translationService = $translationService;
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
        $response = $handler->handle($request);

        if ($response instanceof ClientResponse) {
            $this->translationService->translateEntities();

            $serializedResponse = $this->serializer->serialize($response->getResponse(), 'json');
            $response = $response->withSerializedResponse($serializedResponse);
        }
        return $response;
    }
}
