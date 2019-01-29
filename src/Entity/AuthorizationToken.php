<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Entity;

/**
 * The class representing an authorization token.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthorizationToken
{
    /**
     * The agent for which the token was issued.
     * @var string
     */
    protected $agent = '';

    /**
     * The ids of the enabled mod combinations of the token.
     * @var array|int[]
     */
    protected $enabledModCombinationIds = [];

    /**
     * Sets the agent for which the token was issued.
     * @param string $agent
     * @return $this
     */
    public function setAgent(string $agent): self
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * Returns the agent for which the token was issued.
     * @return string
     */
    public function getAgent(): string
    {
        return $this->agent;
    }

    /**
     * Sets the ids of the enabled mod combinations of the token.
     * @param array|int[] $enabledModCombinationIds
     * @return $this
     */
    public function setEnabledModCombinationIds(array $enabledModCombinationIds): self
    {
        $this->enabledModCombinationIds = $enabledModCombinationIds;
        return $this;
    }

    /**
     * Returns the ids of the enabled mod combinations of the token.
     * @return array|int[]
     */
    public function getEnabledModCombinationIds(): array
    {
        return $this->enabledModCombinationIds;
    }
}