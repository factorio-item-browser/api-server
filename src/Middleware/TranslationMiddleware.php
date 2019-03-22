<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware handling the translations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationMiddleware implements MiddlewareInterface
{
    /**
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the middleware class.
     * @param TranslationService $translationService
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorizationToken = $this->getAuthorizationTokenFromRequest($request);
        $authorizationToken->setLocale($this->getLocaleFromRequest($request));

        $response = $handler->handle($request);

        $this->translationService->translateEntities($authorizationToken);
        return $response;
    }

    /**
     * Returns the authorization token used for the request.
     * @param ServerRequestInterface $request
     * @return AuthorizationToken
     */
    protected function getAuthorizationTokenFromRequest(ServerRequestInterface $request): AuthorizationToken
    {
        $result = $request->getAttribute(AuthorizationToken::class);
        if (!$result instanceof AuthorizationToken) {
            $result = new AuthorizationToken();
        }
        return $result;
    }

    /**
     * Returns the locale from the request.
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getLocaleFromRequest(ServerRequestInterface $request): string
    {
        $acceptLanguage = $request->getHeaderLine('Accept-Language');
        return ($acceptLanguage === '') ? Config::DEFAULT_LOCALE : $acceptLanguage;
    }
}
