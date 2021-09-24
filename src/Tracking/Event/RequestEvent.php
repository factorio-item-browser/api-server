<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Tracking\Event;

use BluePsyduck\Ga4MeasurementProtocol\Request\Event\EventInterface;

/**
 * The event representing a request to the API.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RequestEvent implements EventInterface
{
    /**
     * The name of the agent which initiated the request.
     * @var string|null
     */
    public ?string $agentName = null;

    /**
     * The name of the matched route for the request.
     * @var string|null
     */
    public ?string $routeName = null;

    /**
     * The locale used for the request.
     * @var string|null
     */
    public ?string $locale = null;

    /**
     * The runtime of the request, in milliseconds.
     * @var float|null
     */
    public ?float $runtime = null;

    /**
     * The status code the request resulted in.
     * @var int|null
     */
    public ?int $statusCode = null;

    /**
     * The id of the combination of the request.
     * @var string|null
     */
    public ?string $combinationId = null;

    /**
     * The number of mods contained in the requested combination.
     * @var int|null
     */
    public ?int $modCount = null;

    public function getName(): string
    {
        return 'request';
    }

    public function getParams(): array
    {
        return array_filter([
            'agent_name' => $this->agentName,
            'route_name' => $this->routeName,
            'locale' => $this->locale,
            'runtime' => $this->runtime,
            'status_code' => $this->statusCode,
            'combination_id' => $this->combinationId,
            'mod_count' => $this->modCount,
        ], fn($v) => !is_null($v));
    }
}
