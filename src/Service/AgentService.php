<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Server\Entity\Agent;

/**
 * The service managing the agents.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AgentService
{
    /**
     * The known agents.
     * @var array|Agent[]
     */
    protected $agents;

    /**
     * Initializes the service.
     * @param array|Agent[] $agents
     */
    public function __construct(array $agents)
    {
        $this->agents = [];
        foreach ($agents as $agent) {
            $this->agents[$agent->getName()] = $agent;
        }
    }

    public function getByAccessKey(string $name, string $accessKey): ?Agent
    {
        $result = $this->agents[$name] ?? null;
        if ($result instanceof Agent && $result->getAccessKey() !== $accessKey) {
            $result = null;
        }
        return $result;
    }
}
