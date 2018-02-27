<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * The repository of the translation database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class TranslationRepository extends EntityRepository
{
    /**
     * Finds all translations with the specified types and names.
     * @param string $locale
     * @param array|string[] $namesByTypes The names to search, grouped by their types.
     * @param array|int[] $modCombinationIds The IDs of the mod combinations, or empty to use all translations.
     * @return array|string[][]
     */
    public function findAllTranslationsByTypesAndNames(
        string $locale,
        array $namesByTypes,
        array $modCombinationIds
    ): array {
        $columns = [
            't.locale AS locale',
            't.type AS type',
            't.name AS name',
            't.value AS value',
            't.description AS description',
            'mc.order AS order'
        ];

        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select($columns)
                     ->innerJoin('t.modCombination', 'mc')
                     ->andWhere('t.locale IN (:locales)')
                     ->setParameter('locales', [$locale, 'en']);

        if (count($modCombinationIds) > 0) {
            $queryBuilder->andWhere('t.modCombination IN (:modCombinationIds)')
                         ->setParameter('modCombinationIds', array_values($modCombinationIds));
        }

        $index = 0;
        $conditions = [];
        foreach ($namesByTypes as $type => $names) {
            $conditions[] = '(t.type = :type' . $index . ' AND t.name IN (:names' . $index . '))';
            $queryBuilder->setParameter('type' . $index, $type)
                         ->setParameter('names' . $index, array_values($names));
            ++$index;
        }
        $queryBuilder->andWhere('(' . implode(' OR ', $conditions) . ')');

        return $queryBuilder->getQuery()->getResult();
    }
}