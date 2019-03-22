<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\MissingAuthorizationTokenException;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware to check the authorization token.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * The routes which are whitelisted from the authorization.
     * @var array
     */
    protected const WHITELISTED_ROUTES = [
        '/auth'
    ];

    /**
     * The authorization service.
     * @var AuthorizationService
     */
    protected $authorizationService;

    /**
     * Initializes the authorization middleware class.
     * @param AuthorizationService $authorizationService
     */
    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ApiServerException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array($request->getRequestTarget(), self::WHITELISTED_ROUTES, true)) {
            $request = $this->readAuthorizationFromRequest($request);
        }
        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws MissingAuthorizationTokenException
     * @throws ApiServerException
     */
    protected function readAuthorizationFromRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $serializedToken = $this->extractSerializedTokenFromHeader($request->getHeaderLine('Authorization'));
        $token = $this->authorizationService->deserializeToken($serializedToken);

        $request = $request->withAttribute(AuthorizationToken::class, $token);
        return $request;
    }

    /**
     * Extracts the serialized token from the specified Bearer header.
     * @param string $header
     * @return string
     * @throws MissingAuthorizationTokenException
     */
    protected function extractSerializedTokenFromHeader(string $header): string
    {
        if (substr($header, 0, 7) !== 'Bearer ') {
            throw new MissingAuthorizationTokenException();
        }

        return substr($header, 7);
    }
}
