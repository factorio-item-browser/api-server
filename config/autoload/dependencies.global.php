<?php

declare(strict_types=1);

/**
 * The configuration of the project dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use BluePsyduck\ZendAutoWireFactory\AutoWireFactory;
use BluePsyduck\ZendAutoWireFactory\ConfigReaderFactory;
use FactorioItemBrowser\Api\Client\Constant\ServiceName;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use JMS\Serializer\SerializerInterface;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'aliases' => [
            SerializerInterface::class => ServiceName::SERIALIZER,
        ],
        'factories'  => [
            Handler\Auth\AuthHandler::class => AutoWireFactory::class,
            Handler\Generic\GenericDetailsHandler::class => AutoWireFactory::class,
            Handler\Generic\GenericIconHandler::class => AutoWireFactory::class,
            Handler\Item\ItemIngredientHandler::class => AutoWireFactory::class,
            Handler\Item\ItemProductHandler::class => AutoWireFactory::class,
            Handler\Item\ItemRandomHandler::class => AutoWireFactory::class,
            Handler\Mod\ModListHandler::class => AutoWireFactory::class,
            Handler\Mod\ModMetaHandler::class => AutoWireFactory::class,
            Handler\NotFoundHandler::class => InvokableFactory::class,
            Handler\Recipe\RecipeDetailsHandler::class => AutoWireFactory::class,
            Handler\Recipe\RecipeMachinesHandler::class => AutoWireFactory::class,
            Handler\Search\SearchQueryHandler::class => AutoWireFactory::class,

            Mapper\DatabaseItemToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\DatabaseMachineToClientMachineMapper::class => AutoWireFactory::class,
            Mapper\DatabaseModToClientModMapper::class => AutoWireFactory::class,
            Mapper\DatabaseRecipeToClientRecipeMapper::class => AutoWireFactory::class,
            Mapper\DatabaseRecipeToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\MachineDataToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper::class => AutoWireFactory::class,
            Mapper\RecipeDataToGenericEntityMapper::class => AutoWireFactory::class,

            Middleware\TranslationMiddleware::class => AutoWireFactory::class,
            Middleware\AuthorizationMiddleware::class => AutoWireFactory::class,
            Middleware\CleanupMiddleware::class => AutoWireFactory::class,
            Middleware\DocumentationRedirectMiddleware::class => AutoWireFactory::class,
            Middleware\MetaMiddleware::class => AutoWireFactory::class,
            Middleware\RequestDeserializerMiddleware::class => AutoWireFactory::class,
            Middleware\ResponseSerializerMiddleware::class => AutoWireFactory::class,

            ModResolver\ModCombinationResolver::class => AutoWireFactory::class,
            ModResolver\ModDependencyResolver::class => AutoWireFactory::class,

            SearchDecorator\ItemDecorator::class => AutoWireFactory::class,
            SearchDecorator\RecipeDecorator::class => AutoWireFactory::class,

            Service\AgentService::class => Service\AgentServiceFactory::class,
            Service\AuthorizationService::class => AutoWireFactory::class,
            Service\IconService::class => AutoWireFactory::class,
            Service\MachineService::class => AutoWireFactory::class,
            Service\RecipeService::class => AutoWireFactory::class,
            Service\SearchDecoratorService::class => Service\SearchDecoratorServiceFactory::class,
            Service\TranslationService::class => AutoWireFactory::class,

            // Dependencies of other libraries
            BodyParamsMiddleware::class => InvokableFactory::class,
            ErrorResponseGenerator::class => Response\ErrorResponseGeneratorFactory::class,

            // Auto-wire helpers
            'array $mapRouteToRequest' => ConfigReaderFactory::register(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::MAP_ROUTE_TO_REQUEST),
            'string $authorizationKey' => ConfigReaderFactory::register(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::AUTHORIZATION, ConfigKey::AUTHORIZATION_KEY),
            'string $version' => ConfigReaderFactory::register(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::VERSION),
        ],
    ],
];
