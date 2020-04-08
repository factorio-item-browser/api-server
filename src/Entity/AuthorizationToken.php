<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Entity;

use Ramsey\Uuid\UuidInterface;

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
     * The combination id of the token.
     * @var UuidInterface
     */
    protected $combinationId;

    /**
     * The mod names to use with this token.
     * @var array|string[]
     */
    protected $modNames = [];

    /**
     * Whether the data for the combination is available.
     * @var bool
     */
    protected $isDataAvailable = false;

    /**
     * The locale to use for the request.
     * @var string
     */
    protected $locale = '';

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
     * Sets the combination id of the token.
     * @param UuidInterface $combinationId
     * @return $this
     */
    public function setCombinationId(UuidInterface $combinationId): self
    {
        $this->combinationId = $combinationId;
        return $this;
    }

    /**
     * Returns the combination id of the token.
     * @return UuidInterface
     */
    public function getCombinationId(): UuidInterface
    {
        return $this->combinationId;
    }

    /**
     * Sets the mod names to use with this token.
     * @param array|string[] $modNames
     * @return $this
     */
    public function setModNames(array $modNames): self
    {
        $this->modNames = $modNames;
        return $this;
    }

    /**
     * Returns the mod names to use with this token.
     * @return array|string[]
     */
    public function getModNames(): array
    {
        return $this->modNames;
    }

    /**
     * Returns whether the data for the combination is available.
     * @return bool
     */
    public function getIsDataAvailable(): bool
    {
        return $this->isDataAvailable;
    }

    /**
     * Sets whether the data for the combination is available.
     * @param bool $isDataAvailable
     * @return $this
     */
    public function setIsDataAvailable(bool $isDataAvailable): self
    {
        $this->isDataAvailable = $isDataAvailable;
        return $this;
    }

    /**
     * Sets the locale to use for the request.
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the locale to use for the request.
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}
