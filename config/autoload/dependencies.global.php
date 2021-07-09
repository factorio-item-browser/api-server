<?php

/**
 * The configuration of the project dependencies.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
// phpcs:ignoreFile

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Middleware\ErrorResponseGenerator;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Roave\PsrContainerDoctrine\Migrations\ConfigurationLoaderFactory;
use Roave\PsrContainerDoctrine\Migrations\DependencyFactoryFactory;

use function BluePsyduck\LaminasAutoWireFactory\injectAliasArray;
use function BluePsyduck\LaminasAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'aliases' => [
            ErrorResponseGenerator::class => Response\ErrorResponseGenerator::class,
        ],
        'factories'  => [
            Command\CleanCacheCommand::class => AutoWireFactory::class,
            Command\TriggerCombinationUpdatesCommand::class => AutoWireFactory::class,

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
            Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper::class => AutoWireFactory::class,
            Mapper\RecipeDataToGenericEntityMapper::class => AutoWireFactory::class,

            Middleware\AuthorizationMiddleware::class => AutoWireFactory::class,
            Middleware\CleanupMiddleware::class => AutoWireFactory::class,
            Middleware\CombinationMiddleware::class => AutoWireFactory::class,
            Middleware\CorsHeaderMiddleware::class => AutoWireFactory::class,
            Middleware\MetaMiddleware::class => AutoWireFactory::class,
            Middleware\RequestDeserializerMiddleware::class => AutoWireFactory::class,
            Middleware\ResponseSerializerMiddleware::class => AutoWireFactory::class,
            Middleware\TranslationMiddleware::class => AutoWireFactory::class,

            Response\ErrorResponseGenerator::class => AutoWireFactory::class,

            SearchDecorator\ItemDecorator::class => AutoWireFactory::class,
            SearchDecorator\RecipeDecorator::class => AutoWireFactory::class,

            Service\CombinationUpdateService::class => AutoWireFactory::class,
            Service\IconService::class => AutoWireFactory::class,
            Service\MachineService::class => AutoWireFactory::class,
            Service\RecipeService::class => AutoWireFactory::class,
            Service\SearchDecoratorService::class => AutoWireFactory::class,
            Service\TranslationService::class => AutoWireFactory::class,

            // Dependencies of other libraries
            BodyParamsMiddleware::class => AutoWireFactory::class,
            ConfigurationLoader::class => ConfigurationLoaderFactory::class,
            DependencyFactory::class => DependencyFactoryFactory::class,
            ImplicitOptionsMiddleware::class => Middleware\ImplicitOptionsMiddlewareFactory::class,

            // Auto-wire helpers
            'array $agents' => readConfig(ConfigKey::MAIN, ConfigKey::AGENTS),
            'array $allowedOrigins' => readConfig(ConfigKey::MAIN, ConfigKey::ALLOWED_ORIGINS),
            'array $requestClassesByRoutes' => readConfig(ConfigKey::MAIN, ConfigKey::REQUEST_CLASSES_BY_ROUTES),
            'array $searchDecorators' => injectAliasArray(ConfigKey::MAIN, ConfigKey::SEARCH_DECORATORS),
            'bool $isDebug' => readConfig('debug'),
            'string $version' => readConfig('version'),
        ],
    ],
];
