<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Server\Database\Entity\Item;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeIngredient;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeProduct;
use FactorioItemBrowser\Api\Server\Database\Repository\ItemRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\ItemRepository
 */
class ItemRepositoryTest extends TestCase
{
    use ReflectionTrait;

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
        $namesByTypes = ['foo' => ['abc', 'def'], 'bar' => ['ghi']];
        $queryResult = [$this->createMock(Item::class)];

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
                             ->setMethods(['andWhere', 'setParameter', 'innerJoin', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('andWhere')
                     ->with(
                         '((i.type = :type0 AND i.name IN (:names0)) OR (i.type = :type1 AND i.name IN (:names1)))'
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 5 : 4))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['type0', 'foo'],
                         ['names0', ['abc', 'def']],
                         ['type1', 'bar'],
                         ['names1', ['ghi']],
                         ['modCombinationIds', $modCombinationIds]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($expectWhere ? $this->once() : $this->never())
                     ->method('innerJoin')
                     ->with('i.modCombinations', 'mc', 'WITH', 'mc.id IN (:modCombinationIds)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var ItemRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ItemRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('i')
                   ->willReturn($queryBuilder);

        $result = $repository->findByTypesAndNames($namesByTypes, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Tests the findByIds method.
     * @covers ::findByIds
     */
    public function testFindByIds()
    {
        $ids = [42, 1337];
        $queryResult = [$this->createMock(Item::class)];

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
                             ->setMethods(['andWhere', 'setParameter', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('andWhere')
                     ->with('i.id IN (:ids)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with('ids', $ids)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var ItemRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ItemRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('i')
                   ->willReturn($queryBuilder);

        $result = $repository->findByIds($ids);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for the findByKeywords test.
     * @return array
     */
    public function provideFindByKeywords(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findByKeywords method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findByKeywords
     * @dataProvider provideFindByKeywords
     */
    public function testFindByKeywords(array $modCombinationIds, bool $expectWhere)
    {
        $keywords = ['foo', 'b_a\\r%'];
        $queryResult = [$this->createMock(Item::class)];

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
                             ->setMethods(['andWhere', 'setParameter', 'innerJoin', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->exactly(2))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['i.name LIKE :keyword0'],
                         ['i.name LIKE :keyword1']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 3 : 2))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['keyword0', '%foo%'],
                         ['keyword1', '%b\\_a\\\\r\\%%'],
                         ['modCombinationIds', $modCombinationIds]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($expectWhere ? $this->once() : $this->never())
                     ->method('innerJoin')
                     ->with('i.modCombinations', 'mc', 'WITH', 'mc.id IN (:modCombinationIds)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var ItemRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ItemRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('i')
                   ->willReturn($queryBuilder);

        $result = $repository->findByKeywords($keywords, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for the findRandom test.
     * @return array
     */
    public function provideFindRandom(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findRandom method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findRandom
     * @dataProvider provideFindRandom
     */
    public function testFindRandom(array $modCombinationIds, bool $expectWhere)
    {
        $numberOfItems = 42;
        $queryResult = [$this->createMock(Item::class)];

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
                             ->setMethods([
                                 'addSelect',
                                 'addOrderBy',
                                 'setMaxResults',
                                 'innerJoin',
                                 'setParameter',
                                 'getQuery'
                             ])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('addSelect')
                     ->with('RAND() AS HIDDEN rand')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('addOrderBy')
                     ->with('rand')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setMaxResults')
                     ->with($numberOfItems)
                     ->willReturnSelf();
        $queryBuilder->expects($expectWhere ? $this->once() : $this->never())
                     ->method('innerJoin')
                     ->with('i.modCombinations', 'mc', 'WITH', 'mc.id IN (:modCombinationIds)')
                     ->willReturnSelf();
        $queryBuilder->expects($expectWhere ? $this->once() : $this->never())
                     ->method('setParameter')
                     ->with('modCombinationIds', $modCombinationIds)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var ItemRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ItemRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('i')
                   ->willReturn($queryBuilder);

        $result = $repository->findRandom($numberOfItems, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for the removeOrphans test.
     * @return array
     */
    public function provideRemoveOrphans(): array
    {
        return [
            [
                [['id' => 42], ['id' => 1337]],
                [42, 1337]
            ],
            [
                [],
                []
            ]
        ];
    }

    /**
     * Tests the removeOrphans method.
     * @param array $firstResult
     * @param array $expectedItemIds
     * @covers ::removeOrphans
     * @dataProvider provideRemoveOrphans
     */
    public function testRemoveOrphans(array $firstResult, array $expectedItemIds)
    {
        $entityName = 'abc';

        /* @var AbstractQuery|MockObject $query1 */
        $query1 = $this->getMockBuilder(AbstractQuery::class)
                       ->setMethods(['getResult'])
                       ->disableOriginalConstructor()
                       ->getMockForAbstractClass();
        $query1->expects($this->once())
               ->method('getResult')
               ->willReturn($firstResult);

        /* @var QueryBuilder|MockObject $queryBuilder1 */
        $queryBuilder1 = $this->getMockBuilder(QueryBuilder::class)
                              ->setMethods(['select', 'leftJoin', 'andWhere', 'getQuery'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $queryBuilder1->expects($this->once())
                      ->method('select')
                      ->with('i.id AS id')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->exactly(3))
                      ->method('leftJoin')
                      ->withConsecutive(
                          ['i.modCombinations', 'mc'],
                          [RecipeIngredient::class, 'ri', 'WITH', 'ri.item = i.id'],
                          [RecipeProduct::class, 'rp', 'WITH', 'rp.item = i.id']
                      )
                      ->willReturnSelf();
        $queryBuilder1->expects($this->exactly(3))
                      ->method('andWhere')
                      ->withConsecutive(
                          ['mc.id IS NULL'],
                          ['ri.item IS NULL'],
                          ['rp.item IS NULL']
                      )
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query1);

        $queryBuilder2 = null;
        if (count($expectedItemIds) > 0) {
            /* @var AbstractQuery|MockObject $query2 */
            $query2 = $this->getMockBuilder(AbstractQuery::class)
                           ->setMethods(['execute'])
                           ->disableOriginalConstructor()
                           ->getMockForAbstractClass();
            $query2->expects($this->once())
                   ->method('execute');

            /* @var QueryBuilder|MockObject $queryBuilder2 */
            $queryBuilder2 = $this->getMockBuilder(QueryBuilder::class)
                                  ->setMethods(['delete', 'andWhere', 'setParameter', 'getQuery'])
                                  ->disableOriginalConstructor()
                                  ->getMock();
            $queryBuilder2->expects($this->once())
                          ->method('delete')
                          ->with($entityName, 'i')
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('andWhere')
                          ->with('i.id IN (:itemIds)')
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('setParameter')
                          ->with('itemIds', $expectedItemIds)
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('getQuery')
                          ->willReturn($query2);
        }

        /* @var ItemRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ItemRepository::class)
                    ->setMethods(['createQueryBuilder'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->exactly((count($expectedItemIds) > 0) ? 2 : 1))
                   ->method('createQueryBuilder')
                   ->with('i')
                   ->willReturnOnConsecutiveCalls($queryBuilder1, $queryBuilder2);
        $this->injectProperty($repository, '_entityName', $entityName);


        $this->assertSame($repository, $repository->removeOrphans());
    }
}
