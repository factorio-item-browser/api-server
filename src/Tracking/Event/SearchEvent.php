<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Tracking\Event;

use BluePsyduck\Ga4MeasurementProtocol\Attribute\Event;
use BluePsyduck\Ga4MeasurementProtocol\Attribute\Parameter;
use BluePsyduck\Ga4MeasurementProtocol\Request\Event\EventInterface;

/**
 * The event representing a search.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @codeCoverageIgnore -- Should not need any coverage to begin with
 */
#[Event('search')]
class SearchEvent implements EventInterface
{
    /**
     * The locale used for the search.
     */
    #[Parameter('locale')]
    public ?string $locale = null;

    /**
     * The id of the combination used for the search.
     */
    #[Parameter('combination_id')]
    public ?string $combinationId = null;

    /**
     * The number of mods contained in the combination.
     */
    #[Parameter('mod_count')]
    public ?int $modCount = null;

    /**
     * The query string used for the search.
     */
    #[Parameter('query_string')]
    public ?string $queryString = null;

    /**
     * Whether the search results were already cached.
     */
    #[Parameter('cached')]
    public ?bool $cached = null;

    /**
     * The number of results returned by the search.
     */
    #[Parameter('result_count')]
    public ?int $resultCount = null;

    /**
     * The runtime of the search, in milliseconds.
     */
    #[Parameter('runtime')]
    public ?float $runtime = null;
}
