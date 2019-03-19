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

            Mapper\CombiningRecipeMapper::class => ReflectionFactory::class,
            Mapper\DatabaseItemToGenericEntityMapper::class => ReflectionFactory::class,
            Mapper\DatabaseMachineToClientMachineMapper::class => ReflectionFactory::class,
            Mapper\DatabaseModToClientModMapper::class => ReflectionFactory::class,
            Mapper\DatabaseRecipeToClientRecipeMapper::class => ReflectionFactory::class,
            Mapper\DatabaseRecipeToGenericEntityMapper::class => ReflectionFactory::class,

            Middleware\AcceptLanguageMiddleware::class => ReflectionFactory::class,
            Middleware\AuthorizationMiddleware::class => ReflectionFactory::class,
            Middleware\CleanupMiddleware::class => Middleware\CleanupMiddlewareFactory::class,
            Middleware\DocumentationRedirectMiddleware::class => ReflectionFactory::class,
            Middleware\MetaMiddleware::class => Middleware\MetaMiddlewareFactory::class,
            Middleware\RequestDeserializerMiddleware::class => Middleware\RequestDeserializerMiddlewareFactory::class,
            Middleware\ResponseSerializerMiddleware::class => Middleware\ResponseSerializerMiddlewareFactory::class,

            ModResolver\ModCombinationResolver::class => ReflectionFactory::class,
            ModResolver\ModDependencyResolver::class => ReflectionFactory::class,

            SearchDecorator\ItemDecorator::class => ReflectionFactory::class,
            SearchDecorator\RecipeDecorator::class => ReflectionFactory::class,

            Service\AgentService::class => Service\AgentServiceFactory::class,
            Service\AuthorizationService::class => Service\AuthorizationServiceFactory::class,
            Service\SearchDecoratorService::class => Service\SearchDecoratorServiceFactory::class,

            // Dependencies of other libraries
            BodyParamsMiddleware::class => InvokableFactory::class,
            ErrorResponseGenerator::class => Response\ErrorResponseGeneratorFactory::class,
        ]
    ],
];
