<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Tracking\Event;

use BluePsyduck\Ga4MeasurementProtocol\Request\Event\EventInterface;

/**
 *
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RequestEvent implements EventInterface
{
    public ?string $agentName = null;
    public ?string $routeName = null;
    public ?string $locale = null;
    public ?float $runtime = null;
    public ?int $statusCode = null;

    public ?string $combinationId = null;
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
