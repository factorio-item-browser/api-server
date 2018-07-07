<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Server\Database\Repository\ModCombinationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModCombinationRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\ModCombinationRepository
 */
class ModCombinationRepositoryTest extends TestCase
{
    /**
     * Tests the findByModNames method.
     * @covers ::findByModNames
     */
    public function testFindByModNames()
    {
        $names = ['abc', 'def'];
        $queryResult = [new ModCombination(new Mod('abc'))];

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
                             ->setMethods(['addSelect', 'innerJoin', 'addOrderBy', 'setParameter', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('addSelect')
                     ->with('m')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('innerJoin')
                     ->with('c.mod', 'm', 'WITH', 'm.name IN (:modNames)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with('modNames', $names)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('addOrderBy')
                     ->with('c.order', 'ASC')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var ModCombinationRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ModCombinationRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('c')
                   ->willReturn($queryBuilder);

        $result = $repository->findByModNames($names);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Tests the findModNamesByIds method.
     * @covers ::findModNamesByIds
     */
    public function testFindModNamesByIds()
    {
        $combinationIds = [42, 1337];
        $queryResult = [['name' => 'abc'], ['name' => 'def']];
        $expectedResult = ['abc', 'def'];

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
                             ->setMethods(['select', 'innerJoin', 'addGroupBy', 'setParameter', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with('m.name')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('innerJoin')
                     ->with('c.mod', 'm', 'WITH', 'c.id IN (:combinationIds)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('addGroupBy')
                     ->with('m.name')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with('combinationIds', $combinationIds)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var ModCombinationRepository|MockObject $repository */
        $repository = $this->getMockBuilder(ModCombinationRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('c')
                   ->willReturn($queryBuilder);

        $result = $repository->findModNamesByIds($combinationIds);
        $this->assertSame($expectedResult, $result);
    }
}
