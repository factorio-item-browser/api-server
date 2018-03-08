<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * The entity of the cached search result database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @ORM\Entity(repositoryClass="FactorioItemBrowser\Api\Server\Database\Repository\CachedSearchResultRepository")
 * @ORM\Table(name="CachedSearchResult")
 */
class CachedSearchResult
{
    /**
     * @ORM\Id
     * @ORM\Column(name="hash", type="integer")
     *
     * The hash of the search result.
     * @var int
     */
    protected $hash;

    /**
     * @ORM\Column(name="resultData")
     *
     * The result data of the search.
     * @var string
     */
    protected $resultData;

    /**
     * @ORM\Column(name="lastSearchTime", type="datetime")
     *
     * The time when the search result was last used.
     * @var DateTime
     */
    protected $lastSearchTime;

    /**
     * Initializes the entity.
     * @param int $hash
     */
    public function __construct(int $hash)
    {
        $this->hash = $hash;
        $this->lastSearchTime = new DateTime();
    }

    /**
     * Sets the hash of the search result.
     * @param int $hash
     * @return $this
     */
    public function setHash(int $hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Returns the hash of the search result.
     * @return int
     */
    public function getHash(): int
    {
        return $this->hash;
    }

    /**
     * Sets the result data of the search.
     * @param string $resultData
     * @return $this
     */
    public function setResultData(string $resultData)
    {
        $this->resultData = $resultData;
        return $this;
    }

    /**
     * Returns the result data of the search.
     * @return string
     */
    public function getResultData(): string
    {
        return $this->resultData;
    }

    /**
     * Sets the time when the search result was last used.
     * @param DateTime $lastSearchTime
     * @return $this
     */
    public function setLastSearchTime(DateTime $lastSearchTime)
    {
        $this->lastSearchTime = $lastSearchTime;
        return $this;
    }

    /**
     * Returns the time when the search result was last used.
     * @return DateTime
     */
    public function getLastSearchTime(): DateTime
    {
        return $this->lastSearchTime;
    }
}