<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * The repository class of the item database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemRepository extends EntityRepository
{
    /**
     * Finds the id data of the items with the specified types and names.
     * @param array|string[][] $namesByTypes
     * @param array|int[] $modCombinationIds
     * @return array
     */
    public function findIdDataByTypesAndNames(array $namesByTypes, array $modCombinationIds = []): array
    {
        $columns = [
            'i.id AS id',
            'i.type AS type',
            'i.name AS name'
        ];

        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder->select($columns);

        $index = 0;
        $conditions = [];
        foreach ($namesByTypes as $type => $names) {
            $conditions[] = '(i.type = :type' . $index . ' AND i.name IN (:names' . $index . '))';
            $queryBuilder->setParameter('type' . $index, $type)
                         ->setParameter('names' . $index, array_values($names));
            ++$index;
        }
        $queryBuilder->andWhere('(' . implode(' OR ', $conditions) . ')');

        if (count($modCombinationIds) > 0) {
            $queryBuilder->andWhere('i.modCombinations IN (:modCombinationIds)')
                         ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }
}