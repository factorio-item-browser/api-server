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
        $this->agents = $agents;
    }

    /**
     * Returns the agent with the access key, if it actually matches.
     * @param string $accessKey
     * @return Agent|null
     */
    public function getByAccessKey(string $accessKey): ?Agent
    {
        if ($accessKey === '') {
            return null;
        }

        foreach ($this->agents as $agent) {
            if ($agent->getAccessKey() === $accessKey) {
                return $agent;
            }
        }
        return null;
    }

    /**
     * Returns the agent with the specified name.
     * @param string $name
     * @return Agent|null
     */
    public function getByName(string $name): ?Agent
    {
        foreach ($this->agents as $agent) {
            if ($agent->getName() === $name) {
                return $agent;
            }
        }
        return null;
    }
}
