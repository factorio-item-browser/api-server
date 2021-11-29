<?php

/**
 * The file providing the routes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
// phpcs:ignoreFile

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use FactorioItemBrowser\Api\Server\Constant\RouteName;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->post('/{combination-id}', Handler\Meta\StatusHandler::class, RouteName::META_STATUS);

    $app->post('/{combination-id}/generic/details', Handler\Generic\GenericDetailsHandler::class, RouteName::GENERIC_DETAILS);
    $app->post('/{combination-id}/generic/icon', Handler\Generic\GenericIconHandler::class, RouteName::GENERIC_ICON);

    $app->post('/{combination-id}/item/ingredient', Handler\Item\ItemIngredientHandler::class, RouteName::ITEM_INGREDIENT);
    $app->post('/{combination-id}/item/list', Handler\Item\ItemListHandler::class, RouteName::ITEM_LIST);
    $app->post('/{combination-id}/item/product', Handler\Item\ItemProductHandler::class, RouteName::ITEM_PRODUCT);
    $app->post('/{combination-id}/item/random', Handler\Item\ItemRandomHandler::class, RouteName::ITEM_RANDOM);

    $app->post('/{combination-id}/mod/list', Handler\Mod\ModListHandler::class, RouteName::MOD_LIST);

    $app->post('/{combination-id}/recipe/details', Handler\Recipe\RecipeDetailsHandler::class, RouteName::RECIPE_DETAILS);
    $app->post('/{combination-id}/recipe/list', Handler\Recipe\RecipeListHandler::class, RouteName::RECIPE_LIST);
    $app->post('/{combination-id}/recipe/machines', Handler\Recipe\RecipeMachinesHandler::class, RouteName::RECIPE_MACHINES);

    $app->post('/{combination-id}/search/query', Handler\Search\SearchQueryHandler::class, RouteName::SEARCH_QUERY);
};
