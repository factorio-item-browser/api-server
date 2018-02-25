<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Constant;

/**
 * The flags of the mod combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModCombinationFlag
{
    /**
     * The flag that the mod combination has items.
     */
    const HAS_ITEMS = 'hasItems';

    /**
     * The flag that the mod combination has recipes.
     */
    const HAS_RECIPES = 'hasRecipes';

    /**
     * The flag that the mod combination icons.
     */
    const HAS_ICONS = 'hasIcons';

    /**
     * The flag that the mod combination has translations.
     */
    const HAS_TRANSLATIONS = 'hasTranslations';
}