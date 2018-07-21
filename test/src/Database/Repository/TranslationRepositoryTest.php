<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Server\Database\Repository\TranslationRepository;
use FactorioItemBrowser\Api\Server\Search\Result\ResultPriority;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the TranslationRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\TranslationRepository
 */
class TranslationRepositoryTest extends TestCase
{
    /**
     * Provides the data for the findByTypesAndNames test.
     * @return array
     */
    public function provideFindByTypesAndNames(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findByTypesAndNames method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findByTypesAndNames
     * @dataProvider provideFindByTypesAndNames
     */
    public function testFindByTypesAndNames(array $modCombinationIds, bool $expectWhere)
    {
        $locale = 'xyz';
        $namesByTypes = [
            EntityType::RECIPE => ['abc', 'def'],
            EntityType::MACHINE => ['ghi'],
            EntityType::ITEM => ['jkl', 'mno']
        ];
        $queryResult = [['abc' => 'def']];

        $condition = '(((t.type = :type0 OR t.isDuplicatedByRecipe = 1) AND t.name IN (:names0))'
            . ' OR ((t.type = :type1 OR t.isDuplicatedByMachine = 1) AND t.name IN (:names1))'
            . ' OR (t.type = :type2 AND t.name IN (:names2)))';

        /* @var AbstractQuery|MockObject $query */
        $query = $this->getMockBuilder(AbstractQuery::class)
                      ->setMethods(['getResult'])
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();
        $query->expects($this->once())
              ->method('getResult')
              ->willReturn($queryResult);

        /* @var QueryBuilder|MockObject $queryBuilder */
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
                             ->setMethods(['select', 'innerJoin', 'andWhere', 'setParameter', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with([
                         't.locale AS locale',
                         't.type AS type',
                         't.name AS name',
                         't.value AS value',
                         't.description AS description',
                         't.isDuplicatedByRecipe AS isDuplicatedByRecipe',
                         't.isDuplicatedByMachine AS isDuplicatedByMachine',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('innerJoin')
                     ->with('t.modCombination', 'mc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 3 : 2))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['t.locale IN (:locales)'],
                         [$condition],
                         ['(t.modCombination IN (:modCombinationIds) OR t.type = :typeMod)']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 9 : 7))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['locales', [$locale, 'en']],
                         ['type0', EntityType::RECIPE],
                         ['names0', ['abc', 'def']],
                         ['type1', EntityType::MACHINE],
                         ['names1', ['ghi']],
                         ['type2', EntityType::ITEM],
                         ['names2', ['jkl', 'mno']],
                         ['modCombinationIds', $modCombinationIds],
                         ['typeMod', 'mod']
                     )
                     ->willReturnSelf();

        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var TranslationRepository|MockObject $repository */
        $repository = $this->getMockBuilder(TranslationRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('t')
                   ->willReturn($queryBuilder);

        $result = $repository->findByTypesAndNames($locale, $namesByTypes, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for the findTypesAndNamesByKeywords test.
     * @return array
     */
    public function provideFindTypesAndNamesByKeywords(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findTypesAndNamesByKeywords method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findTypesAndNamesByKeywords
     * @dataProvider provideFindTypesAndNamesByKeywords
     */
    public function testFindTypesAndNamesByKeywords(array $modCombinationIds, bool $expectWhere)
    {
        $locale = 'xyz';
        $keywords = ['foo', 'b_a\\r%'];
        $queryResult = [['abc' => 'def']];

        $priorityColumn = 'MIN(CASE WHEN t.locale = :localePrimary THEN :priorityPrimary '
            . 'WHEN t.locale = :localeSecondary THEN :prioritySecondary ELSE :priorityAny END) AS priority';

        /* @var AbstractQuery|MockObject $query */
        $query = $this->getMockBuilder(AbstractQuery::class)
                      ->setMethods(['getResult'])
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();
        $query->expects($this->once())
              ->method('getResult')
              ->willReturn($queryResult);

        /* @var QueryBuilder|MockObject $queryBuilder */
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
                             ->setMethods(['select', 'andWhere', 'addGroupBy', 'setParameter', 'innerJoin', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with([
                         't.type AS type',
                         't.name AS name',
                         $priorityColumn
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(3))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['t.type IN (:types)'],
                         ['LOWER(CONCAT(t.type, t.name, t.value, t.description)) LIKE :keyword0'],
                         ['LOWER(CONCAT(t.type, t.name, t.value, t.description)) LIKE :keyword1']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('addGroupBy')
                     ->withConsecutive(
                         ['t.type'],
                         ['t.name']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 9 : 8))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['localePrimary', $locale],
                         ['localeSecondary', 'en'],
                         ['priorityPrimary', ResultPriority::PRIMARY_LOCALE_MATCH],
                         ['prioritySecondary', ResultPriority::SECONDARY_LOCALE_MATCH],
                         ['priorityAny', ResultPriority::ANY_MATCH],
                         ['types', [EntityType::ITEM, EntityType::FLUID, EntityType::RECIPE]],
                         ['keyword0', '%foo%'],
                         ['keyword1', '%b\\_a\\\\r\\%%'],
                         ['modCombinationIds', $modCombinationIds]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($expectWhere ? $this->once() : $this->never())
                     ->method('innerJoin')
                     ->with('t.modCombination', 'mc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var TranslationRepository|MockObject $repository */
        $repository = $this->getMockBuilder(TranslationRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('t')
                   ->willReturn($queryBuilder);

        $result = $repository->findTypesAndNamesByKeywords($locale, $keywords, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }
}
