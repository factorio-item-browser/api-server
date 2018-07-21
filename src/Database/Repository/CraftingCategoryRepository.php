<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\CraftingCategory;

/**
 * The repository class of the crafting category database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CraftingCategoryRepository extends EntityRepository
{
    /**
     * Finds the crafting categories with the specified names.
     * @param array|string[] $names
     * @return array|CraftingCategory[]
     */
    public function findByNames(array $names): array
    {
        $queryBuilder = $this->createQueryBuilder('cc');
        $queryBuilder->andWhere('cc.name IN (:names)')
                     ->setParameter('names', $names);

        return $queryBuilder->getQuery()->getResult();
    }
}
