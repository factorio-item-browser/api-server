<?php

declare(strict_types=1);

/**
 * The configuration of the project dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use Blast\ReflectionFactory\ReflectionFactory;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories'  => [
            Database\Service\CachedSearchResultService::class => ReflectionFactory::class,
            Database\Service\IconService::class => ReflectionFactory::class,
            Database\Service\ItemService::class => ReflectionFactory::class,
            Database\Service\MachineService::class => ReflectionFactory::class,
            Database\Service\ModService::class => ReflectionFactory::class,
            Database\Service\RecipeService::class => ReflectionFactory::class,
            Database\Service\TranslationService::class => ReflectionFactory::class,

            Handler\Auth\AuthHandler::class => ReflectionFactory::class,
            Handler\Generic\GenericDetailsHandler::class => ReflectionFactory::class,
            Handler\Generic\GenericIconHandler::class => ReflectionFactory::class,
            Handler\Item\ItemIngredientHandler::class => ReflectionFactory::class,
            Handler\Item\ItemProductHandler::class => ReflectionFactory::class,
            Handler\Item\ItemRandomHandler::class => ReflectionFactory::class,
            Handler\Mod\ModListHandler::class => ReflectionFactory::class,
            Handler\Mod\ModMetaHandler::class => ReflectionFactory::class,
            Handler\NotFoundHandler::class => InvokableFactory::class,
            Handler\Recipe\RecipeDetailsHandler::class => ReflectionFactory::class,
            Handler\Recipe\RecipeMachinesHandler::class => ReflectionFactory::class,
            Handler\Search\SearchQueryHandler::class => ReflectionFactory::class,

            Mapper\ItemMapper::class => ReflectionFactory::class,
            Mapper\MachineMapper::class => ReflectionFactory::class,
            Mapper\ModMapper::class => ReflectionFactory::class,
            Mapper\RecipeMapper::class => ReflectionFactory::class,

            Middleware\AcceptLanguageMiddleware::class => ReflectionFactory::class,
            Middleware\AuthorizationMiddleware::class => ReflectionFactory::class,
            Middleware\CleanupMiddleware::class => Middleware\CleanupMiddlewareFactory::class,
            Middleware\DocumentationRedirectMiddleware::class => ReflectionFactory::class,
            Middleware\MetaMiddleware::class => Middleware\MetaMiddlewareFactory::class,

            Search\Handler\DuplicateRecipeHandler::class => InvokableFactory::class,
            Search\Handler\ItemHandler::class => ReflectionFactory::class,
            Search\Handler\MissingItemIdHandler::class => ReflectionFactory::class,
            Search\Handler\MissingRecipeIdHandler::class => ReflectionFactory::class,
            Search\Handler\ProductRecipeHandler::class => ReflectionFactory::class,
            Search\Handler\RecipeHandler::class => ReflectionFactory::class,
            Search\Handler\SearchHandlerManager::class => Search\Handler\SearchHandlerManagerFactory::class,
            Search\Handler\TranslationHandler::class => ReflectionFactory::class,
            Search\SearchDecorator::class => ReflectionFactory::class,

            Service\AgentService::class => Service\AgentServiceFactory::class,
            Service\AuthorizationService::class => Service\AuthorizationServiceFactory::class,

            // Dependencies of other libraries
            BodyParamsMiddleware::class => InvokableFactory::class,
            ErrorResponseGenerator::class => Response\ErrorResponseGeneratorFactory::class,
        ]
    ],
];
