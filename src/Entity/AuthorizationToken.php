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
     * The name of the agent for which the token was issued.
     * @var string
     */
    protected $agentName = '';

    /**
     * The ids of the enabled mod combinations of the token.
     * @var array|int[]
     */
    protected $enabledModCombinationIds = [];

    /**
     * The locale to use for the request.
     * @var string
     */
    protected $locale;

    /**
     * Sets the name of the agent for which the token was issued.
     * @param string $agentName
     * @return $this
     */
    public function setAgentName(string $agentName): self
    {
        $this->agentName = $agentName;
        return $this;
    }

    /**
     * Returns the name of the agent for which the token was issued.
     * @return string
     */
    public function getAgentName(): string
    {
        return $this->agentName;
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

    /**
     * Sets the locale to use for the request
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the locale to use for the request
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}
