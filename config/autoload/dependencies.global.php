<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use ContainerInteropDoctrine\EntityManagerFactory;
use Doctrine\ORM\EntityManager;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories'  => [
            BodyParamsMiddleware::class => InvokableFactory::class,
            EntityManager::class => EntityManagerFactory::class,
            ErrorResponseGenerator::class => Error\ErrorResponseGeneratorFactory::class,

            Database\Service\ModService::class => Database\Service\AbstractDatabaseServiceFactory::class,
            Error\MessageLogger::class => InvokableFactory::class,
            Handler\AuthHandler::class => Handler\AuthHandlerFactory::class,
            Handler\ModListHandler::class => Handler\ModListHandlerFactory::class,
            Handler\NotFoundHandler::class => InvokableFactory::class,
            Middleware\AuthorizationMiddleware::class => Middleware\AuthorizationMiddlewareFactory::class,
            Middleware\MetaMiddleware::class => Middleware\MetaMiddlewareFactory::class,
        ],
    ],
];
