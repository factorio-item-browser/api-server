<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Result;

use FactorioItemBrowser\Api\Client\Constant\EntityType;

/**
 * The class representing a recipe search result.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeResult extends AbstractResult
{
    /**
     * Returns the ID of the entity.
     * @return int
     */
    public function getId(): int
    {
        return (int) current($this->recipeIds);
    }

    /**
     * Returns the type of the search result.
     * @return string
     */
    public function getType(): string
    {
        return EntityType::RECIPE;
    }
}