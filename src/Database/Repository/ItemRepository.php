<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\Item;

/**
 * The repository class of the item database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemRepository extends EntityRepository
{
    /**
     * Finds the items with the specified types and names.
     * @param array|string[][] $namesByTypes
     * @param array|int[] $modCombinationIds
     * @return array|Item[]
     */
    public function findByTypesAndNames(array $namesByTypes, array $modCombinationIds = []): array
    {
        $queryBuilder = $this->createQueryBuilder('i');

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
            $queryBuilder->innerJoin('i.modCombinations', 'mc', 'WITH', 'mc.id IN (:modCombinationIds)')
                         ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the items with the specified ids.
     * @param array|int[] $ids
     * @return array|Item[]
     */
    public function findByIds(array $ids): array
    {
        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder->andWhere('i.id IN (:ids)')
                     ->setParameter('ids', array_values($ids));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the items matching the specified keywords.
     * @param array|string[] $keywords
     * @param array|int[] $modCombinationIds
     * @return array|Item[]
     */
    public function findByKeywords(array $keywords, array $modCombinationIds = []): array
    {
        $queryBuilder = $this->createQueryBuilder('i');

        $index = 0;
        foreach ($keywords as $keyword) {
            $queryBuilder->andWhere('i.name LIKE :keyword' . $index)
                         ->setParameter('keyword' . $index, '%' . addcslashes($keyword, '\\%_') . '%');
            ++$index;
        }

        if (count($modCombinationIds) > 0) {
            $queryBuilder
                ->innerJoin('i.modCombinations', 'mc', 'WITH', 'mc.id IN (:modCombinationIds)')
                ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds random items.
     * @param int $numberOfItems
     * @param array|int[] $modCombinationIds
     * @return array|Item[]
     */
    public function findRandom(int $numberOfItems, array $modCombinationIds = []): array
    {
        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder->addSelect('RAND() AS HIDDEN rand')
                     ->addOrderBy('rand')
                     ->setMaxResults($numberOfItems);

        if (count($modCombinationIds) > 0) {
            $queryBuilder
                ->innerJoin('i.modCombinations', 'mc', 'WITH', 'mc.id IN (:modCombinationIds)')
                ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }
}