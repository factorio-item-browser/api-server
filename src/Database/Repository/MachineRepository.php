<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\Machine;

/**
 * The repository class of the machine database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineRepository extends EntityRepository
{
    /**
     * Finds the id data of the machines with the specified names.
     * @param array|string[] $names
     * @param array|int[] $modCombinationIds
     * @return array
     */
    public function findIdDataByNames(array $names, array $modCombinationIds = []): array
    {
        $columns = [
            'm.id AS id',
            'm.name AS name',
            'mc.order AS order'
        ];

        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->select($columns)
                     ->innerJoin('m.modCombinations', 'mc')
                     ->andWhere('m.name IN (:names)')
                     ->setParameter('names', array_values($names));

        if (count($modCombinationIds) > 0) {
            $queryBuilder
                ->andWhere('mc.id IN (:modCombinationIds)')
                ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the id data of the machines supporting the specified crafting categories.
     * @param array|string[] $craftingCategories
     * @param array|int[] $modCombinationIds
     * @return array
     */
    public function findIdDataByCraftingCategories(array $craftingCategories, array $modCombinationIds = []): array
    {
        $columns = [
            'm.id AS id',
            'm.name AS name',
            'mc.order AS order'
        ];

        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->select($columns)
                     ->innerJoin('m.craftingCategories', 'cc')
                     ->innerJoin('m.modCombinations', 'mc')
                     ->andWhere('cc.name IN (:craftingCategories)')
                     ->setParameter('craftingCategories', array_values($craftingCategories));

        if (count($modCombinationIds) > 0) {
            $queryBuilder
                ->andWhere('mc.id IN (:modCombinationIds)')
                ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the machines of the specified IDs, including all details.
     * @param array|int[] $ids
     * @return array|Machine[]
     */
    public function findByIds(array $ids): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->addSelect('cc')
                     ->leftJoin('m.craftingCategories', 'cc')
                     ->andWhere('m.id IN (:ids)')
                     ->setParameter('ids', array_values($ids));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Removes any orphaned machines, i.e. machines no longer used by any combination.
     * @return $this
     */
    public function removeOrphans()
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->select('m.id AS id')
                     ->leftJoin('m.modCombinations', 'mc')
                     ->andWhere('mc.id IS NULL');

        $machineIds = [];
        foreach ($queryBuilder->getQuery()->getResult() as $data) {
            $machineIds[] = $data['id'];
        }

        if (count($machineIds) > 0) {
            $queryBuilder = $this->createQueryBuilder('m');
            $queryBuilder->delete($this->_entityName, 'm')
                         ->andWhere('m.id IN (:machineIds)')
                         ->setParameter('machineIds', array_values($machineIds));
            $queryBuilder->getQuery()->execute();
        }
        return $this;
    }
}
