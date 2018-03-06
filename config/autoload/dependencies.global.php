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

            Database\Service\ItemService::class => Database\Service\AbstractModsAwareServiceFactory::class,
            Database\Service\ModService::class => Database\Service\AbstractDatabaseServiceFactory::class,
            Database\Service\RecipeService::class => Database\Service\AbstractModsAwareServiceFactory::class,
            Database\Service\TranslationService::class => Database\Service\AbstractModsAwareServiceFactory::class,

            Handler\Auth\AuthHandler::class => Handler\Auth\AuthHandlerFactory::class,
            Handler\Generic\GenericDetailsHandler::class => Handler\Generic\GenericDetailsHandlerFactory::class,
            Handler\Generic\GenericIconHandler::class => Handler\Generic\GenericIconHandlerFactory::class,
            Handler\Item\ItemIngredientHandler::class => Handler\Item\AbstractItemRecipeHandlerFactory::class,
            Handler\Item\ItemProductHandler::class => Handler\Item\AbstractItemRecipeHandlerFactory::class,
            Handler\Mod\ModListHandler::class => Handler\Mod\ModListHandlerFactory::class,
            Handler\NotFoundHandler::class => InvokableFactory::class,
            Handler\Recipe\RecipeDetailsHandler::class => Handler\Recipe\RecipeDetailsHandlerFactory::class,
            Handler\Search\SearchQueryHandler::class => Handler\Search\SearchQueryHandlerFactory::class,

            Middleware\AcceptLanguageMiddleware::class => Middleware\AcceptLanguageMiddlewareFactory::class,
            Middleware\AuthorizationMiddleware::class => Middleware\AuthorizationMiddlewareFactory::class,
            Middleware\DocumentationRedirectMiddleware::class => InvokableFactory::class,
            Middleware\MetaMiddleware::class => InvokableFactory::class,
        ],
        'invokables' => [
            ErrorResponseGenerator::class => Response\ErrorResponseGenerator::class,
        ]
    ],
];
