<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Blast\BaseUrl\BaseUrlMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Stratigility\Middleware\DoublePassMiddlewareDecorator;
use Zend\Stratigility\Middleware\ErrorHandler;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->pipe(Middleware\MetaMiddleware::class);
    $app->pipe(ErrorHandler::class);
    $app->pipe(Middleware\DatabaseConfigurationMiddleware::class);
    $app->pipe(Middleware\CleanupMiddleware::class);

    $app->pipe(new DoublePassMiddlewareDecorator(function ($request, $response, $next) use ($container) {
        $middleware = $container->get(BaseUrlMiddleware::class);
        return $middleware($request, $response, $next);
    }, new Response()));
    $app->pipe(ServerUrlMiddleware::class);
    $app->pipe(RouteMiddleware::class);
    $app->pipe(Middleware\DocumentationRedirectMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);
    $app->pipe(Middleware\AuthorizationMiddleware::class);
    $app->pipe(Middleware\AcceptLanguageMiddleware::class);
    $app->pipe(BodyParamsMiddleware::class);

    $app->pipe(DispatchMiddleware::class);
    $app->pipe(Handler\NotFoundHandler::class);
};
