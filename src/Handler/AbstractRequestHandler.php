<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler;

use FactorioItemBrowser\Api\Client\Request\RequestInterface as ClientRequestInterface;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\UnexpectedRequestException;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The abstract class of the request handlers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractRequestHandler implements RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $clientRequest = $this->getClientRequest($request);
        $clientResponse = $this->handleRequest($clientRequest);
        return new ClientResponse($clientResponse);
    }

    /**
     * Returns the client request entity from the server request.
     * @param ServerRequestInterface $request
     * @return ClientRequestInterface
     * @throws UnexpectedRequestException
     */
    protected function getClientRequest(ServerRequestInterface $request): ClientRequestInterface
    {
        $expectedRequestClass = $this->getExpectedRequestClass();
        $clientRequest = $request->getAttribute(ClientRequestInterface::class);
        if (!$clientRequest instanceof $expectedRequestClass) {
            throw new UnexpectedRequestException(
                $expectedRequestClass,
                is_object($clientRequest) ? get_class($clientRequest) : gettype($clientRequest)
            );
        }
        return $clientRequest;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    abstract protected function getExpectedRequestClass(): string;

    /**
     * Creates the response data from the validated request data.
     * @param ClientRequestInterface $clientRequest
     * @return ClientResponseInterface
     * @throws ApiServerException
     */
    abstract protected function handleRequest($clientRequest): ClientResponseInterface;
}
