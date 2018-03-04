<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe;

/**
 * The repository class of the recipe database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeRepository extends EntityRepository
{
    /**
     * Finds the id data of the recipes with the specified names.
     * @param array|string[] $names
     * @param array|int[] $modCombinationIds
     * @return array
     */
    public function findIdDataByNames(array $names, array $modCombinationIds = []): array
    {
        $columns = [
            'r.id AS id',
            'r.name AS name',
            'r.mode AS mode',
            'mc.order AS order'
        ];

        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->select($columns)
                     ->innerJoin('r.modCombinations', 'mc')
                     ->andWhere('r.name IN (:names)')
                     ->setParameter('names', array_values($names));

        if (count($modCombinationIds) > 0) {
            $queryBuilder->andWhere('mc.id IN (:modCombinationIds)')
                         ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the id data of the recipes having the specified items as ingredients.
     * @param array|int[] $itemIds
     * @param array|int[] $modCombinationIds
     * @return array
     */
    public function findIdDataWithIngredientItemId(array $itemIds, array $modCombinationIds = []): array
    {
        $columns = [
            'r.id AS id',
            'r.name AS name',
            'r.mode AS mode',
            'mc.order AS order'
        ];

        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->select($columns)
                     ->innerJoin('r.ingredients', 'ri')
                     ->innerJoin('r.modCombinations', 'mc')
                     ->andWhere('ri.item IN (:itemIds)')
                     ->setParameter('itemIds', array_values($itemIds))
                     ->addOrderBy('r.name', 'ASC')
                     ->addOrderBy('r.mode', 'ASC');

        if (count($modCombinationIds) > 0) {
            $queryBuilder->andWhere('mc.id IN (:modCombinationIds)')
                         ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds the recipes of the specified IDs, including ingredient and product data.
     * @param array|int[] $ids
     * @return array|Recipe[]
     */
    public function findByIds(array $ids): array
    {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->addSelect('ri', 'rii', 'rp', 'rpi')
                     ->leftJoin('r.ingredients', 'ri')
                     ->leftJoin('ri.item', 'rii')
                     ->leftJoin('r.products', 'rp')
                     ->leftJoin('rp.item', 'rpi')
                     ->andWhere('r.id IN (:ids)')
                     ->setParameter('ids', array_values($ids));

        return $queryBuilder->getQuery()->getResult();
    }
}