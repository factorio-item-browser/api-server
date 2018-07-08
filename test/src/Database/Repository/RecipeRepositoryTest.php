<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Repository;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeIngredient;
use FactorioItemBrowser\Api\Server\Database\Entity\RecipeProduct;
use FactorioItemBrowser\Api\Server\Database\Repository\RecipeRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeRepository class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Repository\RecipeRepository
 */
class RecipeRepositoryTest extends TestCase
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
        $names = ['foo', 'bar'];
        $queryResult = [$this->createMock(Recipe::class)];

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
                         'r.id AS id',
                         'r.name AS name',
                         'r.mode AS mode',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('innerJoin')
                     ->with('r.modCombinations', 'mc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['r.name IN (:names)'],
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

        /* @var RecipeRepository|MockObject $repository */
        $repository = $this->getMockBuilder(RecipeRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('r')
                   ->willReturn($queryBuilder);

        $result = $repository->findIdDataByNames($names, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for the findIdDataWithIngredientItemId test.
     * @return array
     */
    public function provideFindIdDataWithIngredientItemId(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findIdDataWithIngredientItemId method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findIdDataWithIngredientItemId
     * @dataProvider provideFindIdDataWithIngredientItemId
     */
    public function testFindIdDataWithIngredientItemId(array $modCombinationIds, bool $expectWhere)
    {
        $itemIds = [42, 1337];
        $queryResult = [$this->createMock(Recipe::class)];

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
                             ->setMethods(['select', 'innerJoin', 'andWhere', 'setParameter', 'addOrderBy', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with([
                         'r.id AS id',
                         'r.name AS name',
                         'r.mode AS mode',
                         'IDENTITY(ri.item) AS itemId',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('innerJoin')
                     ->withConsecutive(
                         ['r.ingredients', 'ri'],
                         ['r.modCombinations', 'mc']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['ri.item IN (:itemIds)'],
                         ['mc.id IN (:modCombinationIds)']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['itemIds', $itemIds],
                         ['modCombinationIds', $modCombinationIds]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('addOrderBy')
                     ->withConsecutive(
                         ['r.name', 'ASC'],
                         ['r.mode', 'ASC']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var RecipeRepository|MockObject $repository */
        $repository = $this->getMockBuilder(RecipeRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('r')
                   ->willReturn($queryBuilder);

        $result = $repository->findIdDataWithIngredientItemId($itemIds, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }
    
    /**
     * Provides the data for the findIdDataWithProductItemId test.
     * @return array
     */
    public function provideFindIdDataWithProductItemId(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findIdDataWithProductItemId method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findIdDataWithProductItemId
     * @dataProvider provideFindIdDataWithProductItemId
     */
    public function testFindIdDataWithProductItemId(array $modCombinationIds, bool $expectWhere)
    {
        $itemIds = [42, 1337];
        $queryResult = [$this->createMock(Recipe::class)];

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
                             ->setMethods(['select', 'innerJoin', 'andWhere', 'setParameter', 'addOrderBy', 'getQuery'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $queryBuilder->expects($this->once())
                     ->method('select')
                     ->with([
                         'r.id AS id',
                         'r.name AS name',
                         'r.mode AS mode',
                         'IDENTITY(rp.item) AS itemId',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('innerJoin')
                     ->withConsecutive(
                         ['r.products', 'rp'],
                         ['r.modCombinations', 'mc']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['rp.item IN (:itemIds)'],
                         ['mc.id IN (:modCombinationIds)']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 2 : 1))
                     ->method('setParameter')
                     ->withConsecutive(
                         ['itemIds', $itemIds],
                         ['modCombinationIds', $modCombinationIds]
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
                     ->method('addOrderBy')
                     ->withConsecutive(
                         ['r.name', 'ASC'],
                         ['r.mode', 'ASC']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var RecipeRepository|MockObject $repository */
        $repository = $this->getMockBuilder(RecipeRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('r')
                   ->willReturn($queryBuilder);

        $result = $repository->findIdDataWithProductItemId($itemIds, $modCombinationIds);
        $this->assertSame($queryResult, $result);
    }


    /**
     * Tests the findByIds method.
     * @covers ::findByIds
     */
    public function testFindByIds()
    {
        $ids = [42, 1337];
        $queryResult = [$this->createMock(Recipe::class)];

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
                     ->with('ri', 'rii', 'rp', 'rpi')
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly(4))
                     ->method('leftJoin')
                     ->withConsecutive(
                         ['r.ingredients', 'ri'],
                         ['ri.item', 'rii'],
                         ['r.products', 'rp'],
                         ['rp.item', 'rpi']
                     )
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('andWhere')
                     ->with('r.id IN (:ids)')
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('setParameter')
                     ->with('ids', $ids)
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var RecipeRepository|MockObject $repository */
        $repository = $this->getMockBuilder(RecipeRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('r')
                   ->willReturn($queryBuilder);

        $result = $repository->findByIds($ids);
        $this->assertSame($queryResult, $result);
    }

    /**
     * Provides the data for the findIdDataByKeywords test.
     * @return array
     */
    public function provideFindIdDataByKeywords(): array
    {
        return [
            [[42, 1337], true],
            [[], false]
        ];
    }

    /**
     * Tests the findIdDataByKeywords method.
     * @param array $modCombinationIds
     * @param bool $expectWhere
     * @covers ::findIdDataByKeywords
     * @dataProvider provideFindIdDataByKeywords
     */
    public function testFindIdDataByKeywords(array $modCombinationIds, bool $expectWhere)
    {
        $keywords = ['foo', 'b_a\\r%'];
        $queryResult = [$this->createMock(Recipe::class)];

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
                         'r.id AS id',
                         'r.name AS name',
                         'r.mode AS mode',
                         'mc.order AS order'
                     ])
                     ->willReturnSelf();
        $queryBuilder->expects($this->once())
                     ->method('innerJoin')
                     ->with('r.modCombinations', 'mc')
                     ->willReturnSelf();
        $queryBuilder->expects($this->exactly($expectWhere ? 3 : 2))
                     ->method('andWhere')
                     ->withConsecutive(
                         ['r.name LIKE :keyword0'],
                         ['r.name LIKE :keyword1'],
                         ['mc.id IN (:modCombinationIds)']
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
        $queryBuilder->expects($this->once())
                     ->method('getQuery')
                     ->willReturn($query);

        /* @var RecipeRepository|MockObject $repository */
        $repository = $this->getMockBuilder(RecipeRepository::class)
                           ->setMethods(['createQueryBuilder'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $repository->expects($this->once())
                   ->method('createQueryBuilder')
                   ->with('r')
                   ->willReturn($queryBuilder);

        $result = $repository->findIdDataByKeywords($keywords, $modCombinationIds);
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
     * @param array $expectedRecipeIds
     * @covers ::removeOrphans
     * @dataProvider provideRemoveOrphans
     */
    public function testRemoveOrphans(array $firstResult, array $expectedRecipeIds)
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
                      ->with('r.id AS id')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('leftJoin')
                      ->with('r.modCombinations', 'mc')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('andWhere')
                      ->with('mc.id IS NULL')
                      ->willReturnSelf();
        $queryBuilder1->expects($this->once())
                      ->method('getQuery')
                      ->willReturn($query1);

        $queryBuilder2 = null;
        $queryBuilder3 = null;
        $queryBuilder4 = null;
        if (count($expectedRecipeIds) > 0) {
            /* @var AbstractQuery|MockObject $query2 */
            $query2 = $this->getMockBuilder(AbstractQuery::class)
                           ->setMethods(['execute'])
                           ->disableOriginalConstructor()
                           ->getMockForAbstractClass();
            $query2->expects($this->exactly(3))
                   ->method('execute');

            /* @var QueryBuilder|MockObject $queryBuilder2 */
            $queryBuilder2 = $this->getMockBuilder(QueryBuilder::class)
                                  ->setMethods(['delete', 'andWhere', 'setParameter', 'getQuery'])
                                  ->disableOriginalConstructor()
                                  ->getMock();
            $queryBuilder2->expects($this->once())
                          ->method('delete')
                          ->with(RecipeIngredient::class, 'ri')
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('andWhere')
                          ->with('ri.recipe IN (:recipeIds)')
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('setParameter')
                          ->with('recipeIds', $expectedRecipeIds)
                          ->willReturnSelf();
            $queryBuilder2->expects($this->once())
                          ->method('getQuery')
                          ->willReturn($query2);

            /* @var QueryBuilder|MockObject $queryBuilder3 */
            $queryBuilder3 = $this->getMockBuilder(QueryBuilder::class)
                                  ->setMethods(['delete', 'andWhere', 'setParameter', 'getQuery'])
                                  ->disableOriginalConstructor()
                                  ->getMock();
            $queryBuilder3->expects($this->once())
                          ->method('delete')
                          ->with(RecipeProduct::class, 'rp')
                          ->willReturnSelf();
            $queryBuilder3->expects($this->once())
                          ->method('andWhere')
                          ->with('rp.recipe IN (:recipeIds)')
                          ->willReturnSelf();
            $queryBuilder3->expects($this->once())
                          ->method('setParameter')
                          ->with('recipeIds', $expectedRecipeIds)
                          ->willReturnSelf();
            $queryBuilder3->expects($this->once())
                          ->method('getQuery')
                          ->willReturn($query2);

            /* @var QueryBuilder|MockObject $queryBuilder4 */
            $queryBuilder4 = $this->getMockBuilder(QueryBuilder::class)
                                  ->setMethods(['delete', 'andWhere', 'setParameter', 'getQuery'])
                                  ->disableOriginalConstructor()
                                  ->getMock();
            $queryBuilder4->expects($this->once())
                          ->method('delete')
                          ->with($entityName, 'r')
                          ->willReturnSelf();
            $queryBuilder4->expects($this->once())
                          ->method('andWhere')
                          ->with('r.id IN (:recipeIds)')
                          ->willReturnSelf();
            $queryBuilder4->expects($this->once())
                          ->method('setParameter')
                          ->with('recipeIds', $expectedRecipeIds)
                          ->willReturnSelf();
            $queryBuilder4->expects($this->once())
                          ->method('getQuery')
                          ->willReturn($query2);
        }

        /* @var RecipeRepository|MockObject $repository */
        $repository = $this->getMockBuilder(RecipeRepository::class)
                    ->setMethods(['createQueryBuilder'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->exactly((count($expectedRecipeIds) > 0) ? 4 : 1))
                   ->method('createQueryBuilder')
                   ->with('r')
                   ->willReturnOnConsecutiveCalls(
                       $queryBuilder1,
                       $queryBuilder2,
                       $queryBuilder3,
                       $queryBuilder4
                   );
        $this->injectProperty($repository, '_entityName', $entityName);


        $this->assertSame($repository, $repository->removeOrphans());
    }
}
