<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination;

/**
 * The repository class of the ModCombination database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModCombinationRepository extends EntityRepository
{
    /**
     * Finds the combinations where the specified mod names are the main mod of.
     * @param array|string[] $modNames
     * @return array|ModCombination[]
     */
    public function findAllByModNames(array $modNames): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->addSelect('m')
                     ->innerJoin('c.mod', 'm', 'WITH', 'm.name IN (:modNames)')
                     ->addOrderBy('c.order', 'ASC')
                     ->setParameter('modNames', array_values($modNames));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the mod names of the specified combination ids.
     * @param array|int[] $combinationIds
     * @return array|string[]
     */
    public function findModNamesByIds(array $combinationIds): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->select('m.name')
                     ->innerJoin('c.mod', 'm', 'WITH', 'c.id IN (:combinationIds)')
                     ->addGroupBy('m.name')
                     ->setParameter('combinationIds', array_values($combinationIds));

        $result = [];
        foreach ($queryBuilder->getQuery()->getResult() as $row) {
            $result[] = $row['name'];
        }
        return $result;
    }
}