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
     * @ORM\Column(name="hash")
     *
     * The hash of the search result.
     * @var string
     */
    protected $hash;

    /**
     * @ORM\Column(name="resultData")
     *
     * The result data of the search.
     * @var string
     */
    protected $resultData = '';

    /**
     * @ORM\Column(name="lastSearchTime", type="datetime")
     *
     * The time when the search result was last used.
     * @var DateTime
     */
    protected $lastSearchTime;

    /**
     * Initializes the entity.
     * @param string $hash
     */
    public function __construct(string $hash)
    {
        $this->setHash($hash);
        $this->lastSearchTime = new DateTime();
    }

    /**
     * Sets the hash of the search result.
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash)
    {
        $this->hash = (string) hex2bin($hash);
        return $this;
    }

    /**
     * Returns the hash of the search result.
     * @return string
     */
    public function getHash(): string
    {
        return bin2hex($this->hash);
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
