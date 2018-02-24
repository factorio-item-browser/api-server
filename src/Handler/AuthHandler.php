<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * The handler of the /auth request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthHandler implements RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $key = 'wuppdi';

        $token = [
            'iat' => time(),
            'exp' => time() + 86400,
            'lgn' => 'facpsodaikpsdoakgposdkgposdkgpodsk',
            'mds' => 'foo,bar'
        ];

        $jwt = JWT::encode($token, $key);

        return new JsonResponse([
            'authorization' => $jwt,
            'modHash' => ''
        ]);
    }
}