<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Server\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Server\Database\Repository\CraftingCategoryRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the CraftingCategoryRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\CraftingCategoryRepository
 */
class CraftingCategoryRepositoryTest extends TestCase
{
    /**
     * Tests the findByNames method.
     * @covers ::findByNames
     */
    public function testFindByNames()
    {
        $names = ['abc'];
        $queryResult = [$this->createMock(CraftingCategory::class)];

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
                     ->with('cc.name IN (:names)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with('names', $names)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var CraftingCategoryRepository|MockObject $repository */
        $repository = $this->getMockBuilder(CraftingCategoryRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('cc')
                   ->willReturn($queryBuilder);

        $result = $repository->findByNames($names);
        $this->assertSame($queryResult, $result);
    }
}
