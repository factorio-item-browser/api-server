<?php

declare(strict_types=1);

/**
 * The file providing the routes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use FactorioItemBrowser\Api\Server\Constant\RouteName;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->post('/auth', Handler\Auth\AuthHandler::class, RouteName::AUTH);

    $app->post('/generic/details', Handler\Generic\GenericDetailsHandler::class, RouteName::GENERIC_DETAILS);
    $app->post('/generic/icon', Handler\Generic\GenericIconHandler::class, RouteName::GENERIC_ICON);

    $app->post('/item/ingredient', Handler\Item\ItemIngredientHandler::class, RouteName::ITEM_INGREDIENT);
    $app->post('/item/product', Handler\Item\ItemProductHandler::class, RouteName::ITEM_PRODUCT);
    $app->post('/item/random', Handler\Item\ItemRandomHandler::class, RouteName::ITEM_RANDOM);

    $app->post('/mod/list', Handler\Mod\ModListHandler::class, RouteName::MOD_LIST);

    $app->post('/recipe/details', Handler\Recipe\RecipeDetailsHandler::class, RouteName::RECIPE_DETAILS);
    $app->post('/recipe/machines', Handler\Recipe\RecipeMachinesHandler::class, RouteName::RECIPE_MACHINES);

    $app->post('/search/query', Handler\Search\SearchQueryHandler::class, RouteName::SEARCH_QUERY);
};
