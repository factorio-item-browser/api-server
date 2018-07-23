<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\Icon;

/**
 * The repository class of the icon database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconRepository extends EntityRepository
{
    /**
     * Finds the hash data of the specified entities.
     * @param array|string[][] $namesByTypes
     * @param array|int[] $modCombinationIds
     * @return array
     */
    public function findHashDataByTypesAndNames(array $namesByTypes, array $modCombinationIds = []): array
    {
        $columns = [
            'IDENTITY(i.file) AS hash',
            'i.type AS type',
            'i.name AS name',
            'mc.order AS order'
        ];

        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder->select($columns)
                     ->innerJoin('i.modCombination', 'mc');

        $index = 0;
        $conditions = [];
        foreach ($namesByTypes as $type => $names) {
            $conditions[] = '(i.type = :type' . $index . ' AND i.name IN (:names' . $index . '))';
            $queryBuilder
                ->setParameter('type' . $index, $type)
                ->setParameter('names' . $index, array_values($names));
            ++$index;
        }
        $queryBuilder->andWhere('(' . implode(' OR ', $conditions) . ')');

        if (count($modCombinationIds) > 0) {
            $queryBuilder
                ->andWhere('mc.id IN (:modCombinationIds)')
                ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the ID data of the icons with the specified hashes.
     * @param array|string[] $hashes
     * @param array|int[] $modCombinationIds
     * @return array
     */
    public function findIdDataByHashes(array $hashes, array $modCombinationIds = []): array
    {
        $columns = [
            'i.id AS id',
            'i.type AS type',
            'i.name AS name',
            'mc.order AS order'
        ];

        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder->select($columns)
                     ->innerJoin('i.modCombination', 'mc')
                     ->andWhere('i.file IN (:hashes)')
                     ->setParameter('hashes', array_map('hex2bin', array_values($hashes)));

        if (count($modCombinationIds) > 0) {
            $queryBuilder->andWhere('mc.id IN (:modCombinationIds)')
                         ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the icons by their id.
     * @param array|int[] $ids
     * @return array|Icon[]
     */
    public function findByIds(array $ids): array
    {
        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder->andWhere('i.id IN (:ids)')
                     ->setParameter('ids', array_values($ids));

        return $queryBuilder->getQuery()->getResult();
    }
}
