<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\CachedSearchResult;

/**
 * The repository class of the cached search result database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CachedSearchResultRepository extends EntityRepository
{
    /**
     * The timeout to use for the cache, in seconds.
     */
    const CACHE_TIMEOUT = 3600;

    /**
     * Finds the search results with the specified hash.
     * @param string $hash
     * @return CachedSearchResult|null
     */
    public function findByHash(string $hash): ?CachedSearchResult
    {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->andWhere('r.hash = :hash')
                     ->andWhere('r.lastSearchTime > :timeCut')
                     ->setParameter('hash', hex2bin($hash))
                     ->setParameter('timeCut', $this->getTimeCut());

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Cleans up no longer needed data.
     * @return $this
     */
    public function cleanup()
    {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->delete($this->_entityName, 'r')
                     ->andWhere('r.lastSearchTime < :timeCut')
                     ->setParameter('timeCut', $this->getTimeCut());

        $queryBuilder->getQuery()->execute();
        return $this;
    }

    /**
     * Returns the time cut timestamp.
     * @return DateTime
     */
    protected function getTimeCut(): DateTime
    {
        return new DateTime('-' . self::CACHE_TIMEOUT . 'seconds');
    }

    /**
     * Clears the database table, emptying the cache.
     * @return $this
     */
    public function clear()
    {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->delete($this->_entityName, 'r');
        $queryBuilder->getQuery()->execute();
        return $this;
    }
}