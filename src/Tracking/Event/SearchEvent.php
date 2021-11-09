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
     * @var string|null
     */
    #[Parameter('locale')]
    public ?string $locale = null;

    /**
     * The id of the combination used for the search.
     * @var string|null
     */
    #[Parameter('combination_id')]
    public ?string $combinationId = null;

    /**
     * The number of mods contained in the combination.
     * @var int|null
     */
    #[Parameter('mod_count')]
    public ?int $modCount = null;

    /**
     * The query string used for the search.
     * @var string|null
     */
    #[Parameter('query_string')]
    public ?string $queryString = null;

    /**
     * Whether the search results were already cached.
     * @var bool|null
     */
    #[Parameter('cached')]
    public ?bool $cached = null;

    /**
     * The number of results returned by the search.
     * @var int|null
     */
    #[Parameter('result_count')]
    public ?int $resultCount = null;

    /**
     * @var float|null The runtime of the search, in milliseconds.
     */
    #[Parameter('runtime')]
    public ?float $runtime = null;
}
