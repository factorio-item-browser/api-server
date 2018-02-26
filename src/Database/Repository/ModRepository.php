<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod;

/**
 * The repository class of the Mod database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModRepository extends EntityRepository
{
    /**
     * Finds all mods with the specified names, fetching their dependencies as well.
     * @param array|string[] $modNames
     * @return array|Mod[]
     */
    public function findAllByNamesWithDependencies(array $modNames)
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->addSelect('d')
                     ->addSelect('dm')
                     ->leftJoin('m.dependencies', 'd', 'WITH', 'm.name IN (:names)')
                     ->leftJoin('d.requiredMod', 'dm')
                     ->setParameter('names', array_values($modNames));

        return $queryBuilder->getQuery()->getResult();
    }
}