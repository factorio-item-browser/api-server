<?php

/**
 * The configuration of the API server itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server;

use FactorioItemBrowser\Api\Client\Request;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\Constant\RouteName;

return [
    ConfigKey::MAIN => [
        ConfigKey::REQUEST_CLASSES_BY_ROUTES => [
            RouteName::GENERIC_DETAILS => Request\Generic\GenericDetailsRequest::class,
            RouteName::GENERIC_ICON => Request\Generic\GenericIconRequest::class,
            RouteName::ITEM_INGREDIENT => Request\Item\ItemIngredientRequest::class,
            RouteName::ITEM_LIST => Request\Item\ItemListRequest::class,
            RouteName::ITEM_PRODUCT => Request\Item\ItemProductRequest::class,
            RouteName::ITEM_RANDOM => Request\Item\ItemRandomRequest::class,
            RouteName::META_STATUS => Request\Meta\StatusRequest::class,
            RouteName::MOD_LIST => Request\Mod\ModListRequest::class,
            RouteName::RECIPE_DETAILS => Request\Recipe\RecipeDetailsRequest::class,
            RouteName::RECIPE_LIST => Request\Recipe\RecipeListRequest::class,
            RouteName::RECIPE_MACHINES => Request\Recipe\RecipeMachinesRequest::class,
            RouteName::SEARCH_QUERY => Request\Search\SearchQueryRequest::class,
        ],
        ConfigKey::SEARCH_DECORATORS => [
            SearchDecorator\ItemDecorator::class,
            SearchDecorator\RecipeDecorator::class,
        ],
    ],
];
