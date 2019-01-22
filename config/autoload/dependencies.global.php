<?php

declare(strict_types=1);

/**
 * The configuration of the project dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories'  => [
            Database\Service\CachedSearchResultService::class => Database\Service\CachedSearchResultServiceFactory::class,
            Database\Service\IconService::class => Database\Service\IconServiceFactory::class,
            Database\Service\ItemService::class => Database\Service\ItemServiceFactory::class,
            Database\Service\MachineService::class => Database\Service\MachineServiceFactory::class,
            Database\Service\ModService::class => Database\Service\ModServiceFactory::class,
            Database\Service\RecipeService::class => Database\Service\RecipeServiceFactory::class,
            Database\Service\TranslationService::class => Database\Service\TranslationServiceFactory::class,

            Handler\Auth\AuthHandler::class => Handler\Auth\AuthHandlerFactory::class,
            Handler\Generic\GenericDetailsHandler::class => Handler\Generic\GenericDetailsHandlerFactory::class,
            Handler\Generic\GenericIconHandler::class => Handler\Generic\GenericIconHandlerFactory::class,
            Handler\Item\ItemIngredientHandler::class => Handler\Item\AbstractItemRecipeHandlerFactory::class,
            Handler\Item\ItemProductHandler::class => Handler\Item\AbstractItemRecipeHandlerFactory::class,
            Handler\Item\ItemRandomHandler::class => Handler\Item\ItemRandomHandlerFactory::class,
            Handler\Mod\ModListHandler::class => Handler\Mod\ModListHandlerFactory::class,
            Handler\Mod\ModMetaHandler::class => Handler\Mod\ModMetaHandlerFactory::class,
            Handler\NotFoundHandler::class => InvokableFactory::class,
            Handler\Recipe\RecipeDetailsHandler::class => Handler\Recipe\RecipeDetailsHandlerFactory::class,
            Handler\Recipe\RecipeMachinesHandler::class => Handler\Recipe\RecipeMachinesHandlerFactory::class,
            Handler\Search\SearchQueryHandler::class => Handler\Search\SearchQueryHandlerFactory::class,

            Mapper\ItemMapper::class => Mapper\AbstractMapperFactory::class,
            Mapper\MachineMapper::class => Mapper\AbstractMapperFactory::class,
            Mapper\ModMapper::class => Mapper\AbstractMapperFactory::class,
            Mapper\RecipeMapper::class => Mapper\AbstractMapperFactory::class,

            Middleware\AcceptLanguageMiddleware::class => Middleware\AcceptLanguageMiddlewareFactory::class,
            Middleware\AuthorizationMiddleware::class => Middleware\AuthorizationMiddlewareFactory::class,
            Middleware\CleanupMiddleware::class => Middleware\CleanupMiddlewareFactory::class,
            Middleware\DocumentationRedirectMiddleware::class => Middleware\DocumentationRedirectMiddlewareFactory::class,
            Middleware\MetaMiddleware::class => Middleware\MetaMiddlewareFactory::class,

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
            BodyParamsMiddleware::class => InvokableFactory::class,
            ErrorResponseGenerator::class => Response\ErrorResponseGeneratorFactory::class,
        ]
    ],
];
