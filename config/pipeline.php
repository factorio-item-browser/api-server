<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\PathBasedRoutingMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Stratigility\Middleware\ErrorHandler;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->pipe(Middleware\MetaMiddleware::class);
    $app->pipe(ErrorHandler::class);
    $app->pipe(ServerUrlMiddleware::class);

    // Register the routing middleware in the middleware pipeline
    $app->pipe(PathBasedRoutingMiddleware::class);

    $app->pipe(Middleware\AuthorizationMiddleware::class);

    // The following handle routing failures for common conditions:
    // - method not allowed
    // - HEAD request but no routes answer that method
    // - OPTIONS request but no routes answer that method
    $app->pipe(MethodNotAllowedMiddleware::class);
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);

    // Seed the UrlHelper with the routing results:
    $app->pipe(UrlHelperMiddleware::class);
    $app->pipe(BodyParamsMiddleware::class);

    // Add more middleware here that needs to introspect the routing results; this
    // might include:
    //
    // - route-based authentication
    // - route-based validation
    // - etc.
    // Register the dispatch middleware in the middleware pipeline
    $app->pipe(DispatchMiddleware::class);

    // At this point, if no Response is returned by any middleware, the
    // NotFoundHandler kicks in; alternately, you can provide other fallback
    // middleware to execute.
    $app->pipe(Handler\NotFoundHandler::class);
};
