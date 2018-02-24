<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Error\MessageLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * The middleware adding the meta node to the response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MetaMiddleware implements MiddlewareInterface
{
    /**
     * The message logger.
     * @var MessageLogger
     */
    protected $messageLogger;

    /**
     * The start time of the execution.
     * @var float
     */
    protected $startTime;

    /**
     * Initializes the meta middleware.
     * @param MessageLogger $messageLogger
     */
    public function __construct(MessageLogger $messageLogger)
    {
        $this->messageLogger = $messageLogger;
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
        if ($response instanceof JsonResponse) {
            $data = $response->getPayload();
            if (!is_array($data)) {
                $data = [];
            }
            $data['meta'] = [
                'statusCode' => $response->getStatusCode(),
                'executionTime' => round(microtime(true) - $this->startTime, 3),
                'messages' => $this->messageLogger->getMessages()
            ];
            $response = new JsonResponse($data, $response->getStatusCode(), $response->getHeaders());
        }
        return $response;
    }
}