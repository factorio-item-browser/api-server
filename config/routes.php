<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->post('/auth', Handler\Auth\AuthHandler::class, 'auth');
    $app->post('/generic/details', Handler\Generic\GenericDetailsHandler::class, 'generic.details');
    $app->post('/generic/icon', Handler\Generic\GenericIconHandler::class, 'generic.icon');
    $app->post('/import', Handler\Import\ImportHandler::class, 'import');
    $app->post('/item/ingredient', Handler\Item\ItemIngredientHandler::class, 'item.ingredient');
    $app->post('/item/product', Handler\Item\ItemProductHandler::class, 'item.product');
    $app->post('/item/random', Handler\Item\ItemRandomHandler::class, 'item.random');
    $app->post('/mod/list', Handler\Mod\ModListHandler::class, 'mod.list');
    $app->post('/mod/meta', Handler\Mod\ModMetaHandler::class, 'mod.meta');
    $app->post('/recipe/details', Handler\Recipe\RecipeDetailsHandler::class, 'recipe.details');
    $app->post('/search/query', Handler\Search\SearchQueryHandler::class, 'search.query');
};
