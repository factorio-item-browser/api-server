<?php

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
    public function findAllByModNames(array $modNames) {
        $queryBuilder = $this->createQueryBuilder('mc');
        $queryBuilder->addSelect('m')
                     ->innerJoin('mc.mod', 'm', 'WITH', 'm.name IN (:modNames)')
                     ->addOrderBy('mc.order', 'ASC')
                     ->setParameter('modNames', array_values($modNames));

        return $queryBuilder->getQuery()->getResult();
    }
}