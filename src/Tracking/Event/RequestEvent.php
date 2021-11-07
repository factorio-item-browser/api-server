<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Tracking\Event;

use BluePsyduck\Ga4MeasurementProtocol\Attribute\Event;
use BluePsyduck\Ga4MeasurementProtocol\Attribute\Parameter;
use BluePsyduck\Ga4MeasurementProtocol\Request\Event\EventInterface;

/**
 * The event representing a request to the API.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
#[Event('request')]
class RequestEvent implements EventInterface
{
    /**
     * The name of the agent which initiated the request.
     * @var string|null
     */
    #[Parameter('agent_name')]
    public ?string $agentName = null;

    /**
     * The name of the matched route for the request.
     * @var string|null
     */
    #[Parameter('route_name')]
    public ?string $routeName = null;

    /**
     * The locale used for the request.
     * @var string|null
     */
    #[Parameter('locale')]
    public ?string $locale = null;

    /**
     * The runtime of the request, in milliseconds.
     * @var float|null
     */
    #[Parameter('runtime')]
    public ?float $runtime = null;

    /**
     * The status code the request resulted in.
     * @var int|null
     */
    #[Parameter('status_code')]
    public ?int $statusCode = null;

    /**
     * The id of the combination of the request.
     * @var string|null
     */
    #[Parameter('combination_id')]
    public ?string $combinationId = null;

    /**
     * The number of mods contained in the requested combination.
     * @var int|null
     */
    #[Parameter('mod_count')]
    public ?int $modCount = null;
}
