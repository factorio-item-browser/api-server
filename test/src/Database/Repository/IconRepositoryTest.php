<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Server\Database\Entity\Icon;
use FactorioItemBrowser\Api\Server\Database\Entity\IconFile;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Server\Database\Repository\IconRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\IconRepository
 */
class IconRepositoryTest extends TestCase
{
    /**
     * Tests the findByIds method.
     * @covers ::findByIds
     */
    public function testFindByIds()
    {
        $ids = [42, 1337];
        $queryResult = [new Icon(new ModCombination(new Mod('abc')), new IconFile('ab12cd34'))];

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

        /* @var IconRepository|MockObject $repository */
        $repository = $this->getMockBuilder(IconRepository::class)
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
}
