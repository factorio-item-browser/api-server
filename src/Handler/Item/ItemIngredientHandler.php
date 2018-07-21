<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Server\Database\Entity\Item as DatabaseItem;

/**
 * The handler of the /item/ingredient request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemIngredientHandler extends AbstractItemRecipeHandler
{
    /**
     * Fetches the IDs of the grouped recipes.
     * @param DatabaseItem $item
     * @return array|int[][]
     */
    protected function fetchGroupedRecipeIds(DatabaseItem $item): array
    {
        return $this->recipeService->getIdsWithIngredient($item->getId());
    }
}
