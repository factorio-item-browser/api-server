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
     * Provides the data for testFindHashDataByTypesAndNames.
     * @return array
     */
    public function provideFindHashDataByTypesAndNames(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findHashDataByTypesAndNames method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findHashDataByTypesAndNames
     * @dataProvider provideFindHashDataByTypesAndNames
     */
    public function testFindHashDataByTypesAndNames(array $modCombinationIds, bool $expectWhere)
    {
        $namesByTypes = ['foo' => ['abc', 'def'], 'bar' => ['ghi']];
        $queryResult = [['jkl' => 'mno'], ['pqr' => 'stu']];

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
                         'IDENTITY(i.file) AS hash',
                         'i.type AS type',
                         'i.name AS name',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('innerJoin')
                     ->with('i.modCombination', 'mc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['((i.type = :type0 AND i.name IN (:names0)) OR (i.type = :type1 AND i.name IN (:names1)))'],
                         ['mc.id IN (:modCombinationIds)']
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

        $result = $repository->findHashDataByTypesAndNames($namesByTypes, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for testFindIdDataByHashes.
     * @return array
     */
    public function provideFindIdDataByHashes(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findIdDataByHashes method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findIdDataByHashes
     * @dataProvider provideFindIdDataByHashes
     */
    public function testFindIdDataByHashes(array $modCombinationIds, bool $expectWhere)
    {
        $hashes = ['12ab34cd', 'ab12cd34'];
        $expectedHashes = [hex2bin('12ab34cd'), hex2bin('ab12cd34')];
        $queryResult = [['jkl' => 'mno'], ['pqr' => 'stu']];

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
                         'i.id AS id',
                         'i.type AS type',
                         'i.name AS name',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('innerJoin')
                     ->with('i.modCombination', 'mc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['i.file IN (:hashes)'],
                         ['mc.id IN (:modCombinationIds)']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['hashes', $expectedHashes],
                         ['modCombinationIds', $modCombinationIds]
                     )
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

        $result = $repository->findIdDataByHashes($hashes, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

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
