<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Entity;

/**
 * The class representing an agent of the API server.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class Agent
{
    /**
     * The name of the agent.
     * @var string
     */
    protected $name = '';

    /**
     * The access key of the agent.
     * @var string
     */
    protected $accessKey = '';

    /**
     * Whether the agent is for demonstration only.
     * @var bool
     */
    protected $isDemo = false;

    /**
     * Sets the name of the agent.
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name of the agent.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the access key of the agent.
     * @param string $accessKey
     * @return $this
     */
    public function setAccessKey(string $accessKey): self
    {
        $this->accessKey = $accessKey;
        return $this;
    }

    /**
     * Returns the access key of the agent.
     * @return string
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * Sets whether the agent is for demonstration only.
     * @param bool $isDemo
     * @return $this
     */
    public function setIsDemo(bool $isDemo): self
    {
        $this->isDemo = $isDemo;
        return $this;
    }

    /**
     * Returns whether the agent is for demonstration only.
     * @return bool
     */
    public function getIsDemo(): bool
    {
        return $this->isDemo;
    }
}
