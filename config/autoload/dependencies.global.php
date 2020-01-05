<?php

declare(strict_types=1);

/**
 * The configuration of the project dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use BluePsyduck\ContainerInteropDoctrineMigrations\MigrationsConfigurationFactory;
use BluePsyduck\ZendAutoWireFactory\AutoWireFactory;
use function BluePsyduck\ZendAutoWireFactory\injectAliasArray;
use function BluePsyduck\ZendAutoWireFactory\readConfig;
use FactorioItemBrowser\Api\Client\Constant\ServiceName;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use JMS\Serializer\SerializerInterface;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'aliases' => [
            ErrorResponseGenerator::class => Response\ErrorResponseGenerator::class,
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
            Handler\NotFoundHandler::class => InvokableFactory::class,
            Handler\Recipe\RecipeDetailsHandler::class => AutoWireFactory::class,
            Handler\Recipe\RecipeMachinesHandler::class => AutoWireFactory::class,
            Handler\Search\SearchQueryHandler::class => AutoWireFactory::class,

            Mapper\DatabaseItemToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\DatabaseMachineToClientMachineMapper::class => AutoWireFactory::class,
            Mapper\DatabaseMachineToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\DatabaseModToClientModMapper::class => AutoWireFactory::class,
            Mapper\DatabaseRecipeToClientRecipeMapper::class => AutoWireFactory::class,
            Mapper\DatabaseRecipeToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper::class => AutoWireFactory::class,
            Mapper\RecipeDataToGenericEntityMapper::class => AutoWireFactory::class,

            Middleware\TranslationMiddleware::class => AutoWireFactory::class,
            Middleware\AuthorizationMiddleware::class => AutoWireFactory::class,
            Middleware\CleanupMiddleware::class => AutoWireFactory::class,
            Middleware\MetaMiddleware::class => AutoWireFactory::class,
            Middleware\RequestDeserializerMiddleware::class => AutoWireFactory::class,
            Middleware\ResponseSerializerMiddleware::class => AutoWireFactory::class,

            Response\ErrorResponseGenerator::class => AutoWireFactory::class,

            SearchDecorator\ItemDecorator::class => AutoWireFactory::class,
            SearchDecorator\RecipeDecorator::class => AutoWireFactory::class,

            Service\AgentService::class => Service\AgentServiceFactory::class,
            Service\AuthorizationService::class => AutoWireFactory::class,
            Service\IconService::class => AutoWireFactory::class,
            Service\MachineService::class => AutoWireFactory::class,
            Service\RecipeService::class => AutoWireFactory::class,
            Service\SearchDecoratorService::class => AutoWireFactory::class,
            Service\TranslationService::class => AutoWireFactory::class,

            // Dependencies of other libraries
            BodyParamsMiddleware::class => AutoWireFactory::class,

            'doctrine.migrations.orm_default' => MigrationsConfigurationFactory::class,

            // Auto-wire helpers
            'array $mapRouteToRequest' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::MAP_ROUTE_TO_REQUEST),
            'array $searchDecorators' => injectAliasArray(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::SEARCH_DECORATORS),

            'bool $isDebug' => readConfig('debug'),

            'int $authorizationTokenLifetime' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::AUTHORIZATION, ConfigKey::AUTHORIZATION_TOKEN_LIFETIME),

            'string $authorizationKey' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::AUTHORIZATION, ConfigKey::AUTHORIZATION_KEY),
            'string $version' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::VERSION),
        ],
    ],
];
