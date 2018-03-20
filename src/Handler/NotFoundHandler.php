<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler;

use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The handler throwing a 404 error as last instance.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class NotFoundHandler implements RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ApiServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new ApiServerException('API endpoint not found: ' . $request->getRequestTarget(), 404);
    }
}