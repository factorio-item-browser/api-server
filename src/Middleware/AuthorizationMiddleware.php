<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Exception;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use Firebase\JWT\JWT;
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
    protected $whitelistedRoutes = [
        '/auth'
    ];

    /**
     * The mod service.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the authorization middleware class.
     * @param ModService $modService
     */
    public function __construct(ModService $modService)
    {
        $this->modService = $modService;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array($request->getRequestTarget(), $this->whitelistedRoutes)) {
            $authorization = $request->getHeaderLine('Authorization');
            if (substr($authorization, 0, 7) !== 'Bearer ') {
                throw new ApiServerException('Authorization token is missing.', 401);
            }

            try {
                $token = JWT::decode(substr($authorization, 7), 'wuppdi', ['HS256']);
                $this->modService->setEnabledModCombinationIds(array_map('intval', $token->mds));
            } catch (Exception $e) {
                throw new ApiServerException('Authorization token is invalid.', 401);
            }
        }
        return $handler->handle($request);
    }
}