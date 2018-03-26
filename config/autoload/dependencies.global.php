<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Blast\BaseUrl\BasePathHelper;
use Blast\BaseUrl\BaseUrlMiddleware;
use Blast\BaseUrl\BaseUrlMiddlewareFactory;
use ContainerInteropDoctrine\EntityManagerFactory;
use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories'  => [
            Database\Service\CachedSearchResultService::class => Database\Service\CachedSearchResultServiceFactory::class,
            Database\Service\IconService::class => Database\Service\AbstractModsAwareServiceFactory::class,
            Database\Service\ItemService::class => Database\Service\AbstractModsAwareServiceFactory::class,
            Database\Service\ModService::class => Database\Service\ModServiceFactory::class,
            Database\Service\RecipeService::class => Database\Service\AbstractModsAwareServiceFactory::class,
            Database\Service\TranslationService::class => Database\Service\AbstractModsAwareServiceFactory::class,

            Handler\Auth\AuthHandler::class => Handler\Auth\AuthHandlerFactory::class,
            Handler\Generic\GenericDetailsHandler::class => Handler\Generic\GenericDetailsHandlerFactory::class,
            Handler\Generic\GenericIconHandler::class => Handler\Generic\GenericIconHandlerFactory::class,
            Handler\Item\ItemIngredientHandler::class => Handler\Item\AbstractItemRecipeHandlerFactory::class,
            Handler\Item\ItemProductHandler::class => Handler\Item\AbstractItemRecipeHandlerFactory::class,
            Handler\Item\ItemRandomHandler::class => Handler\Item\ItemRandomHandlerFactory::class,
            Handler\Import\ImportHandler::class => Handler\Import\ImportHandlerFactory::class,
            Handler\Mod\ModListHandler::class => Handler\Mod\ModListHandlerFactory::class,
            Handler\NotFoundHandler::class => InvokableFactory::class,
            Handler\Recipe\RecipeDetailsHandler::class => Handler\Recipe\RecipeDetailsHandlerFactory::class,
            Handler\Search\SearchQueryHandler::class => Handler\Search\SearchQueryHandlerFactory::class,

            Import\CombinationImporter::class => Import\CombinationImporterFactory::class,
            Import\IconImporter::class => Import\IconImporterFactory::class,
            Import\ImporterManager::class => Import\ImporterManagerFactory::class,
            Import\ItemImporter::class => Import\ItemImporterFactory::class,
            Import\ModImporter::class => Import\ModImporterFactory::class,
            Import\OrderImporter::class => Import\OrderImporterFactory::class,
            Import\RecipeImporter::class => Import\RecipeImporterFactory::class,
            Import\TranslationImporter::class => Import\TranslationImporterFactory::class,

            Middleware\AcceptLanguageMiddleware::class => Middleware\AcceptLanguageMiddlewareFactory::class,
            Middleware\AuthorizationMiddleware::class => Middleware\AuthorizationMiddlewareFactory::class,
            Middleware\CleanupMiddleware::class => Middleware\CleanupMiddlewareFactory::class,
            Middleware\DatabaseConfigurationMiddleware::class => Middleware\DatabaseConfigurationMiddlewareFactory::class,
            Middleware\DocumentationRedirectMiddleware::class => Middleware\DocumentationRedirectMiddlewareFactory::class,
            Middleware\MetaMiddleware::class => InvokableFactory::class,

            Search\Handler\DuplicateRecipeHandler::class => InvokableFactory::class,
            Search\Handler\ItemHandler::class => Search\Handler\ItemHandlerFactory::class,
            Search\Handler\MissingItemIdHandler::class => Search\Handler\MissingItemIdHandlerFactory::class,
            Search\Handler\MissingRecipeIdHandler::class => Search\Handler\MissingRecipeIdHandlerFactory::class,
            Search\Handler\ProductRecipeHandler::class => Search\Handler\ProductRecipeHandlerFactory::class,
            Search\Handler\RecipeHandler::class => Search\Handler\RecipeHandlerFactory::class,
            Search\Handler\SearchHandlerManager::class => Search\Handler\SearchHandlerManagerFactory::class,
            Search\Handler\TranslationHandler::class => Search\Handler\TranslationHandlerFactory::class,
            Search\SearchDecorator::class => Search\SearchDecoratorFactory::class,

            // Dependencies of other libraries
            BasePathHelper::class => InvokableFactory::class,
            BaseUrlMiddleware::class => BaseUrlMiddlewareFactory::class,
            BodyParamsMiddleware::class => InvokableFactory::class,
            EntityManager::class => EntityManagerFactory::class,
            ExportDataService::class => ExportData\ExportDataServiceFactory::class,
        ],
        'invokables' => [
            ErrorResponseGenerator::class => Response\ErrorResponseGenerator::class,
        ]
    ],
];
