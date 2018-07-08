<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Server\Database\Entity\ModDependency;
use FactorioItemBrowser\Api\Server\Database\Repository\ModRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\ModRepository
 */
class ModRepositoryTest extends TestCase
{
    /**
     * Tests the findByNamesWithDependencies method.
     * @covers ::findByNamesWithDependencies
     */
    public function testFindByNamesWithDependencies()
    {
        $names = ['abc', 'def'];
        $queryResult = [$this->createMock(ModDependency::class)];

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
                             ->setMethods(['addSelect', 'leftJoin', 'setParameter', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->exactly(2))
                     ->method('addSelect')
                     ->withConsecutive(
                         ['d'],
                         ['dm']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('leftJoin')
                     ->withConsecutive(
                         ['m.dependencies', 'd', 'WITH', 'm.name IN (:names)'],
                         ['d.requiredMod', 'dm']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with('names', $names)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var ModRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ModRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('m')
                   ->willReturn($queryBuilder);

        $result = $repository->findByNamesWithDependencies($names);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for testCount.
     * @return array
     */
    public function provideCount(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the count method.
     * @param array $modCombinationIds
     * @param bool $expectJoin
     * @covers ::count
     * @dataProvider provideCount
     */
    public function testCount(array $modCombinationIds, bool $expectJoin)
    {
        $queryResult = 42;

        /* @var AbstractQuery|MockObject $query */
        $query = $this->getMockBuilder(AbstractQuery::class)
                      ->setMethods(['getSingleScalarResult'])
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();
        $query->expects($this->once())
              ->method('getSingleScalarResult')
              ->willReturn($queryResult);

                /* @var QueryBuilder|MockObject $queryBuilder */
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
                             ->setMethods(['select', 'innerJoin', 'setParameter', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with('COUNT(DISTINCT m.id) AS numberOfMods')
                     ->willReturnSelf();
        $queryBuilder->expects($expectJoin ? $this->once() : $this->never())
                     ->method('innerJoin')
                     ->with('m.combinations', 'c', 'WITH', 'c.id IN (:modCombinationIds)')
                     ->willReturnSelf();
        $queryBuilder->expects($expectJoin ? $this->once() : $this->never())
                     ->method('setParameter')
                     ->with('modCombinationIds', $modCombinationIds)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var ModRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ModRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('m')
                   ->willReturn($queryBuilder);

        $result = $repository->count($modCombinationIds);
        $this->assertSame($queryResult, $result);
    }
}
