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
     * The known agents by their names.
     * @var array|Agent[]
     */
    protected $agentsByName;

    /**
     * Initializes the service.
     * @param array|Agent[] $agents
     */
    public function __construct(array $agents)
    {
        $this->agentsByName = [];
        foreach ($agents as $agent) {
            $this->agentsByName[$agent->getName()] = $agent;
        }
    }

    /**
     * Returns the agent with the name.
     * @param string $name
     * @return Agent|null
     */
    public function getByName(string $name): ?Agent
    {
        return $this->agentsByName[$name] ?? null;
    }

    /**
     * Returns the agent with the access key, if it actually matches.
     * @param string $name
     * @param string $accessKey
     * @return Agent|null
     */
    public function getByAccessKey(string $name, string $accessKey): ?Agent
    {
        $result = null;
        if ($name !== '' && $accessKey !== ''
            && isset($this->agentsByName[$name])
            && $this->agentsByName[$name]->getAccessKey() === $accessKey
        ) {
            $result = $this->agentsByName[$name];
        }
        return $result;
    }
}
