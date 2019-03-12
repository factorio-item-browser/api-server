<?php

declare(strict_types=1);

/**
 * The configuration of the API server itself.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use FactorioItemBrowser\Api\Client\Request;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\Constant\RouteName;

return [
    ConfigKey::PROJECT => [
        ConfigKey::API_SERVER => [
            ConfigKey::AGENTS => [
                [
                    ConfigKey::AGENT_NAME => 'demo',
                    ConfigKey::AGENT_ACCESS_KEY => 'factorio-item-browser',
                    ConfigKey::AGENT_DEMO => true,
                ],
            ],
            ConfigKey::MAP_ROUTE_TO_REQUEST => [
                RouteName::AUTH => Request\Auth\AuthRequest::class,
                RouteName::GENERIC_DETAILS => Request\Generic\GenericDetailsRequest::class,
                RouteName::GENERIC_ICON => Request\Generic\GenericIconRequest::class,
                RouteName::ITEM_INGREDIENT => Request\Item\ItemIngredientRequest::class,
                RouteName::ITEM_PRODUCT => Request\Item\ItemProductRequest::class,
                RouteName::ITEM_RANDOM => Request\Item\ItemRandomRequest::class,
                RouteName::MOD_LIST => Request\Mod\ModListRequest::class,
                RouteName::MOD_META => Request\Mod\ModMetaRequest::class,
                RouteName::RECIPE_DETAILS => Request\Recipe\RecipeDetailsRequest::class,
                RouteName::RECIPE_MACHINES => Request\Recipe\RecipeMachinesRequest::class,
                RouteName::SEARCH_QUERY => Request\Search\SearchQueryRequest::class,
            ],
            ConfigKey::VERSION => '1.2.0',
        ],
    ],
];