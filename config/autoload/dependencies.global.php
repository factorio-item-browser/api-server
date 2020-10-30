<?php

declare(strict_types=1);

/**
 * The configuration of the project dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use FactorioItemBrowser\Api\Client\Constant\ServiceName;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use JMS\Serializer\SerializerInterface;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Middleware\ErrorResponseGenerator;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Roave\PsrContainerDoctrine\MigrationsConfigurationFactory;

use function BluePsyduck\LaminasAutoWireFactory\injectAliasArray;
use function BluePsyduck\LaminasAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'aliases' => [
            ErrorResponseGenerator::class => Response\ErrorResponseGenerator::class,
            SerializerInterface::class => ServiceName::SERIALIZER,
        ],
        'factories'  => [
            Command\CleanCacheCommand::class => AutoWireFactory::class,
            Command\UpdateCombinationsCommand::class => AutoWireFactory::class,

            Console\Console::class => AutoWireFactory::class,

            Handler\Auth\AuthHandler::class => AutoWireFactory::class,
            Handler\Combination\CombinationExportHandler::class => AutoWireFactory::class,
            Handler\Combination\CombinationStatusHandler::class => AutoWireFactory::class,
            Handler\Combination\CombinationValidateHandler::class => AutoWireFactory::class,
            Handler\Generic\GenericDetailsHandler::class => AutoWireFactory::class,
            Handler\Generic\GenericIconHandler::class => AutoWireFactory::class,
            Handler\Item\ItemIngredientHandler::class => AutoWireFactory::class,
            Handler\Item\ItemListHandler::class => AutoWireFactory::class,
            Handler\Item\ItemProductHandler::class => AutoWireFactory::class,
            Handler\Item\ItemRandomHandler::class => AutoWireFactory::class,
            Handler\Mod\ModListHandler::class => AutoWireFactory::class,
            Handler\NotFoundHandler::class => AutoWireFactory::class,
            Handler\Recipe\RecipeDetailsHandler::class => AutoWireFactory::class,
            Handler\Recipe\RecipeListHandler::class => AutoWireFactory::class,
            Handler\Recipe\RecipeMachinesHandler::class => AutoWireFactory::class,
            Handler\Search\SearchQueryHandler::class => AutoWireFactory::class,

            Mapper\DatabaseItemToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\DatabaseMachineToClientMachineMapper::class => AutoWireFactory::class,
            Mapper\DatabaseMachineToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\DatabaseModToClientModMapper::class => AutoWireFactory::class,
            Mapper\DatabaseRecipeToClientRecipeMapper::class => AutoWireFactory::class,
            Mapper\DatabaseRecipeToGenericEntityMapper::class => AutoWireFactory::class,
            Mapper\ExportJobMapper::class => AutoWireFactory::class,
            Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper::class => AutoWireFactory::class,
            Mapper\RecipeDataToGenericEntityMapper::class => AutoWireFactory::class,

            Middleware\AuthorizationMiddleware::class => AutoWireFactory::class,
            Middleware\CleanupMiddleware::class => AutoWireFactory::class,
            Middleware\CorsHeaderMiddleware::class => AutoWireFactory::class,
            Middleware\MetaMiddleware::class => AutoWireFactory::class,
            Middleware\RequestDeserializerMiddleware::class => AutoWireFactory::class,
            Middleware\ResponseSerializerMiddleware::class => AutoWireFactory::class,
            Middleware\TranslationMiddleware::class => AutoWireFactory::class,

            Response\ErrorResponseGenerator::class => AutoWireFactory::class,

            SearchDecorator\ItemDecorator::class => AutoWireFactory::class,
            SearchDecorator\RecipeDecorator::class => AutoWireFactory::class,

            Service\AgentService::class => Service\AgentServiceFactory::class,
            Service\AuthorizationService::class => AutoWireFactory::class,
            Service\CombinationService::class => AutoWireFactory::class,
            Service\CombinationUpdateService::class => AutoWireFactory::class,
            Service\CombinationValidationService::class => AutoWireFactory::class,
            Service\ExportQueueService::class => AutoWireFactory::class,
            Service\IconService::class => AutoWireFactory::class,
            Service\MachineService::class => AutoWireFactory::class,
            Service\ModPortalService::class => AutoWireFactory::class,
            Service\RecipeService::class => AutoWireFactory::class,
            Service\SearchDecoratorService::class => AutoWireFactory::class,
            Service\TranslationService::class => AutoWireFactory::class,

            // Dependencies of other libraries
            BodyParamsMiddleware::class => AutoWireFactory::class,
            ImplicitOptionsMiddleware::class => Middleware\ImplicitOptionsMiddlewareFactory::class,
            'doctrine.migrations.orm_default' => MigrationsConfigurationFactory::class,

            // Auto-wire helpers
            'array $allowedOrigins' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::ALLOWED_ORIGINS),
            'array $mapRouteToRequest' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::MAP_ROUTE_TO_REQUEST),
            'array $searchDecorators' => injectAliasArray(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::SEARCH_DECORATORS),

            'bool $isDebug' => readConfig('debug'),

            'int $authorizationTokenLifetime' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::AUTHORIZATION, ConfigKey::AUTHORIZATION_TOKEN_LIFETIME),
            'int $maxNumberOfUpdates' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::AUTO_UPDATE, ConfigKey::AUTO_UPDATE_MAX_UPDATES),

            'string $authorizationKey' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::AUTHORIZATION, ConfigKey::AUTHORIZATION_KEY),
            'string $lastUsageInterval' => readConfig(ConfigKey::PROJECT, ConfigKey::API_SERVER, ConfigKey::AUTO_UPDATE, ConfigKey::AUTO_UPDATE_LAST_USAGE_INTERVAL),
            'string $version' => readConfig('version'),
        ],
    ],
];
