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
     * @param array|string[] $hashes
     * @return array|IconFile[]
     */
    public function findByHashes(array $hashes): array
    {
        $queryBuilder = $this->createQueryBuilder('if');
        $queryBuilder->andWhere('if.hash IN (:hashes)')
                     ->setParameter('hashes', array_map('hex2bin', array_values($hashes)));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Removes any orphaned icon files, i.e. icon files no longer used by any icon.
     * @return $this
     */
    public function removeOrphans()
    {
        $queryBuilder = $this->createQueryBuilder('if');
        $queryBuilder->select('if.hash AS hash')
                     ->leftJoin('if.icons', 'i')
                     ->andWhere('i.id IS NULL');

        $hashes = [];
        foreach ($queryBuilder->getQuery()->getResult() as $data) {
            $hashes[] = $data['hash'];
        }

        if (count($hashes) > 0) {
            $queryBuilder = $this->createQueryBuilder('if');
            $queryBuilder->delete($this->_entityName, 'if')
                         ->andWhere('if.hash IN (:hashes)')
                         ->setParameter('hashes', array_values($hashes));
            $queryBuilder->getQuery()->execute();
        }
        return $this;
    }
}
