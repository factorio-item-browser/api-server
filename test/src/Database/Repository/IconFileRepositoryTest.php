<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Server\Database\Entity\IconFile;
use FactorioItemBrowser\Api\Server\Database\Repository\IconFileRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the IconFileRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\IconFileRepository
 */
class IconFileRepositoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the findByHashes method.
     * @covers ::findByHashes
     */
    public function testFindByHashes()
    {
        $hashes = ['12ab34cd', 'ab12cd34'];
        $queryResult = [$this->createMock(IconFile::class)];

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
                     ->with('if.hash IN (:hashes)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with('hashes', [hex2bin('12ab34cd'), hex2bin('ab12cd34')])
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var IconFileRepository|MockObject $repository */
        $repository = $this->getMockBuilder(IconFileRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('if')
                   ->willReturn($queryBuilder);

        $result = $repository->findByHashes($hashes);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for testRemoveOrphans.
     * @return array
     */
    public function provideRemoveOrphans(): array
    {
        return [
            [
                [['hash' => 'abc'], ['hash' => 'def']],
                ['abc', 'def']
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
     * @param array $expectedHashes
     * @covers ::removeOrphans
     * @dataProvider provideRemoveOrphans
     */
    public function testRemoveOrphans(array $firstResult, array $expectedHashes)
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
                      ->with('if.hash AS hash')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('leftJoin')
                      ->with('if.icons', 'i')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('andWhere')
                      ->with('i.id IS NULL')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query1);

        $queryBuilder2 = null;
        if (count($expectedHashes) > 0) {
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
                          ->with($entityName, 'if')
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('andWhere')
                          ->with('if.hash IN (:hashes)')
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('setParameter')
                          ->with('hashes', $expectedHashes)
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('getQuery')
                          ->willReturn($query2);
        }

        /* @var IconFileRepository|MockObject $repository */
        $repository = $this->getMockBuilder(IconFileRepository::class)
                    ->setMethods(['createQueryBuilder'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->exactly((count($expectedHashes) > 0) ? 2 : 1))
                   ->method('createQueryBuilder')
                   ->with('if')
                   ->willReturnOnConsecutiveCalls($queryBuilder1, $queryBuilder2);
        $this->injectProperty($repository, '_entityName', $entityName);


        $this->assertSame($repository, $repository->removeOrphans());
    }
}
