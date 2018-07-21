<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search\Result;

/**
 * The class holding the priorities of the search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ResultPriority
{
    /**
     * The search result is an exact match of the name, the highest priority.
     */
    const EXACT_MATCH = 1;

    /**
     * The search result is matched by the primary locale, i.e. the language of the user.
     */
    const PRIMARY_LOCALE_MATCH = 10;

    /**
     * The search results is matched by the secondary locale, i.e. English.
     */
    const SECONDARY_LOCALE_MATCH = 11;

    /**
     * The search result has no relevance compared to the others.
     */
    const ANY_MATCH = 100;
}
