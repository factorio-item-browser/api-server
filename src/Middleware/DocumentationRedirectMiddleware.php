<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Blast\BaseUrl\BasePathHelper;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Middleware for redirecting to the documentation in case a GET request is encountered.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DocumentationRedirectMiddleware implements MiddlewareInterface
{
    /**
     * The base path helper.
     * @var BasePathHelper
     */
    protected $basePathHelper;

    /**
     * Initializes the middleware.
     * @param BasePathHelper $basePathHelper
     */
    public function __construct(BasePathHelper $basePathHelper)
    {
        $this->basePathHelper = $basePathHelper;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === RequestMethodInterface::METHOD_GET) {
            $result = new RedirectResponse(($this->basePathHelper)('/docs'));
        } else {
            $result = $handler->handle($request);
        }
        return $result;
    }
}