<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories'  => [
            BodyParamsMiddleware::class => InvokableFactory::class,

            Error\MessageLogger::class => InvokableFactory::class,
            ErrorResponseGenerator::class => Error\ErrorResponseGeneratorFactory::class,

            Handler\AuthHandler::class => InvokableFactory::class,
            Handler\NotFoundHandler::class => InvokableFactory::class,

            Middleware\AuthorizationMiddleware::class => InvokableFactory::class,
            Middleware\MetaMiddleware::class => Middleware\MetaMiddlewareFactory::class,
        ],
    ],
];
