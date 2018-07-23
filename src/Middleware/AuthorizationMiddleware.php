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
use stdClass;

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
     * The key used for creating the authorization token.
     * @var string
     */
    protected $authorizationKey;

    /**
     * The mod service.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the authorization middleware class.
     * @param string $authorizationKey
     * @param ModService $modService
     */
    public function __construct(string $authorizationKey, ModService $modService)
    {
        $this->authorizationKey = $authorizationKey;
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
        if (!in_array($request->getRequestTarget(), self::WHITELISTED_ROUTES)) {
            $authorization = $request->getHeaderLine('Authorization');
            if (substr($authorization, 0, 7) !== 'Bearer ') {
                throw new ApiServerException('Authorization token is missing.', 401);
            }

            try {
                $token = $this->decryptToken(substr($authorization, 7));
                $this->modService->setEnabledModCombinationIds(array_map('intval', $token->mds ?? []));

                $request = $request->withAttribute('agent', $token->agt ?? '')
                                   ->withAttribute('allowImport', ($token->imp ?? 0) === 1);
            } catch (Exception $e) {
                throw new ApiServerException('Authorization token is invalid.', 401);
            }
        }
        return $handler->handle($request);
    }

    /**
     * Decrypts the specified token string.
     * @param string $tokenString
     * @return stdClass
     */
    protected function decryptToken(string $tokenString): stdClass
    {
        return JWT::decode($tokenString, $this->authorizationKey, ['HS256']);
    }
}
