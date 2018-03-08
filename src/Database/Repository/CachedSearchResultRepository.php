<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

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
     * Finds the search results with the specified hash.
     * @param int $hash
     * @return CachedSearchResult|null
     */
    public function findByHash(int $hash): ?CachedSearchResult
    {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->andWhere('r.hash = :hash')
                     ->setParameter('hash', $hash);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}