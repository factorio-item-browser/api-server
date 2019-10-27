<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Constant;

/**
 * The interface holding the names of the routes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface RouteName
{
    public const AUTH = 'auth';
    public const EXPORT_CREATE = 'export.create';
    public const EXPORT_STATUS = 'export.status';
    public const GENERIC_DETAILS = 'generic.details';
    public const GENERIC_ICON = 'generic.icon';
    public const ITEM_INGREDIENT = 'item.ingredient';
    public const ITEM_PRODUCT = 'item.product';
    public const ITEM_RANDOM = 'item.random';
    public const MOD_LIST = 'mod.list';
    public const RECIPE_DETAILS = 'recipe.details';
    public const RECIPE_MACHINES = 'recipe.machines';
    public const SEARCH_QUERY = 'search.query';
}
