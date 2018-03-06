<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\IconFile;

/**
 * The repository class of the icon file database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconFileRepository extends EntityRepository
{
    /**
     * Finds the icon files with the specified hashes.
     * @param array|int[] $hashes
     * @return array|IconFile[]
     */
    public function findByHashes(array $hashes): array
    {
        $queryBuilder = $this->createQueryBuilder('if');
        $queryBuilder->andWhere('if.hash IN (:hashes)')
                     ->setParameter('hashes', array_values($hashes));

        return $queryBuilder->getQuery()->getResult();
    }
}