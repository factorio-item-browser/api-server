<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Server\Database\Entity\Machine;
use FactorioItemBrowser\Api\Server\Database\Repository\MachineRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MachineRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\MachineRepository
 */
class MachineRepositoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Provides the data for the findIdDataByNames test.
     * @return array
     */
    public function provideFindIdDataByNames(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findIdDataByNames method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findIdDataByNames
     * @dataProvider provideFindIdDataByNames
     */
    public function testFindIdDataByNames(array $modCombinationIds, bool $expectWhere)
    {
        $names = ['abc', 'def'];
        $queryResult = [$this->createMock(Machine::class)];

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
                         'm.id AS id',
                         'm.name AS name',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('innerJoin')
                     ->with('m.modCombinations', 'mc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['m.name IN (:names)'],
                         ['mc.id IN (:modCombinationIds)']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['names', $names],
                         ['modCombinationIds', $modCombinationIds]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var MachineRepository|MockObject $repository */
        $repository = $this->getMockBuilder(MachineRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('m')
                   ->willReturn($queryBuilder);

        $result = $repository->findIdDataByNames($names, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for the findIdDataByCraftingCategories test.
     * @return array
     */
    public function provideFindIdDataByCraftingCategories(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findIdDataByCraftingCategories method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findIdDataByCraftingCategories
     * @dataProvider provideFindIdDataByCraftingCategories
     */
    public function testFindIdDataByCraftingCategories(array $modCombinationIds, bool $expectWhere)
    {
        $craftingCategories = ['abc', 'def'];
        $queryResult = [$this->createMock(Machine::class)];

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
                         'm.id AS id',
                         'm.name AS name',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('innerJoin')
                     ->withConsecutive(
                         ['m.craftingCategories', 'cc'],
                         ['m.modCombinations', 'mc']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['cc.name IN (:craftingCategories)'],
                         ['mc.id IN (:modCombinationIds)']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['craftingCategories', $craftingCategories],
                         ['modCombinationIds', $modCombinationIds]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var MachineRepository|MockObject $repository */
        $repository = $this->getMockBuilder(MachineRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('m')
                   ->willReturn($queryBuilder);

        $result = $repository->findIdDataByCraftingCategories($craftingCategories, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Tests the findByIds method.
     * @covers ::findByIds
     */
    public function testFindByIds()
    {
        $ids = [42, 1337];
        $queryResult = [$this->createMock(Machine::class)];

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
                             ->setMethods(['addSelect', 'leftJoin', 'andWhere', 'setParameter', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('addSelect')
                     ->with('cc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('leftJoin')
                     ->with('m.craftingCategories', 'cc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('andWhere')
                     ->with('m.id IN (:ids)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with('ids', $ids)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var MachineRepository|MockObject $repository */
        $repository = $this->getMockBuilder(MachineRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('m')
                   ->willReturn($queryBuilder);

        $result = $repository->findByIds($ids);
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
     * @param array $expectedMachineIds
     * @covers ::removeOrphans
     * @dataProvider provideRemoveOrphans
     */
    public function testRemoveOrphans(array $firstResult, array $expectedMachineIds)
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
                      ->with('m.id AS id')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('leftJoin')
                      ->with('m.modCombinations', 'mc')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('andWhere')
                      ->with('mc.id IS NULL')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query1);

        $queryBuilder2 = null;
        if (count($expectedMachineIds) > 0) {
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
                          ->with($entityName, 'm')
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('andWhere')
                          ->with('m.id IN (:machineIds)')
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('setParameter')
                          ->with('machineIds', $expectedMachineIds)
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('getQuery')
                          ->willReturn($query2);
        }

        /* @var MachineRepository|MockObject $repository */
        $repository = $this->getMockBuilder(MachineRepository::class)
                    ->setMethods(['createQueryBuilder'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->exactly((count($expectedMachineIds) > 0) ? 2 : 1))
                   ->method('createQueryBuilder')
                   ->with('m')
                   ->willReturnOnConsecutiveCalls($queryBuilder1, $queryBuilder2);
        $this->injectProperty($repository, '_entityName', $entityName);


        $this->assertSame($repository, $repository->removeOrphans());
    }
}
