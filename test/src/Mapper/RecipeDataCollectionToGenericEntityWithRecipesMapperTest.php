<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeDataCollectionToGenericEntityWithRecipesMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper
 */
class RecipeDataCollectionToGenericEntityWithRecipesMapperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked recipe service.
     * @var RecipeService&MockObject
     */
    protected $recipeService;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipeService = $this->createMock(RecipeService::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     * @covers ::setMapperManager
     */
    public function testConstruct(): void
    {
        $mapper = new RecipeDataCollectionToGenericEntityWithRecipesMapper($this->recipeService);
        $mapper->setMapperManager($this->mapperManager);

        $this->assertSame($this->recipeService, $this->extractProperty($mapper, 'recipeService'));
        $this->assertSame($this->mapperManager, $this->extractProperty($mapper, 'mapperManager'));
    }

    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = RecipeDataCollection::class;

        $mapper = new RecipeDataCollectionToGenericEntityWithRecipesMapper($this->recipeService);
        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = GenericEntityWithRecipes::class;

        $mapper = new RecipeDataCollectionToGenericEntityWithRecipesMapper($this->recipeService);
        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::map
     */
    public function testMap(): void
    {
        $recipeIds = [42, 1337];

        $databaseRecipes = [
            42 => $this->createMock(DatabaseRecipe::class),
            1337 => $this->createMock(DatabaseRecipe::class),
        ];
        $normalRecipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];
        $expensiveRecipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];

        /* @var RecipeDataCollection&MockObject $normalRecipeData */
        $normalRecipeData = $this->createMock(RecipeDataCollection::class);
        /* @var RecipeDataCollection&MockObject $expensiveRecipeData */
        $expensiveRecipeData = $this->createMock(RecipeDataCollection::class);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getAllIds')
                   ->willReturn($recipeIds);
        $recipeData->expects($this->exactly(2))
                   ->method('filterMode')
                   ->withConsecutive(
                       [$this->identicalTo(RecipeMode::NORMAL)],
                       [$this->identicalTo(RecipeMode::EXPENSIVE)]
                   )
                   ->willReturnOnConsecutiveCalls(
                       $normalRecipeData,
                       $expensiveRecipeData
                   );

        /* @var GenericEntityWithRecipes&MockObject $entity */
        $entity = $this->createMock(GenericEntityWithRecipes::class);
        $entity->expects($this->once())
               ->method('setRecipes')
               ->with($this->identicalTo($expensiveRecipes));

        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo($recipeIds))
                            ->willReturn($databaseRecipes);

        /* @var RecipeDataCollectionToGenericEntityWithRecipesMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeDataCollectionToGenericEntityWithRecipesMapper::class)
                       ->setMethods(['mapNormalRecipes', 'mapExpensiveRecipes'])
                       ->setConstructorArgs([$this->recipeService])
                       ->getMock();
        $mapper->setMapperManager($this->mapperManager);
        $mapper->expects($this->once())
               ->method('mapNormalRecipes')
               ->with($this->identicalTo($normalRecipeData))
               ->willReturn($normalRecipes);
        $mapper->expects($this->once())
               ->method('mapExpensiveRecipes')
               ->with($this->identicalTo($normalRecipes), $this->identicalTo($expensiveRecipeData))
               ->willReturn($expensiveRecipes);

        $mapper->map($recipeData, $entity);

        $this->assertSame($databaseRecipes, $this->extractProperty($mapper, 'databaseRecipes'));
    }

    /**
     * Tests the mapNormalRecipes method.
     * @throws ReflectionException
     * @covers ::mapNormalRecipes
     */
    public function testMapNormalRecipes(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData1->expects($this->atLeastOnce())
                    ->method('getId')
                    ->willReturn(42);

        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        $recipeData2->expects($this->atLeastOnce())
                    ->method('getId')
                    ->willReturn(1337);

        /* @var RecipeData&MockObject $recipeData3 */
        $recipeData3 = $this->createMock(RecipeData::class);
        $recipeData3->expects($this->atLeastOnce())
                    ->method('getId')
                    ->willReturn(21);

        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);
        $recipeDataCollection->expects($this->once())
                             ->method('getValues')
                             ->willReturn([$recipeData1, $recipeData2, $recipeData3]);

        /* @var DatabaseRecipe&MockObject $databaseRecipe1 */
        $databaseRecipe1 = $this->createMock(DatabaseRecipe::class);
        /* @var DatabaseRecipe&MockObject $databaseRecipe2 */
        $databaseRecipe2 = $this->createMock(DatabaseRecipe::class);

        $databaseRecipes = [
            42 => $databaseRecipe1,
            21 => $databaseRecipe2,
        ];

        /* @var RecipeWithExpensiveVersion&MockObject $clientRecipe1 */
        $clientRecipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        $clientRecipe1->expects($this->once())
                      ->method('getName')
                      ->willReturn('abc');

        /* @var RecipeWithExpensiveVersion&MockObject $clientRecipe2 */
        $clientRecipe2 = $this->createMock(RecipeWithExpensiveVersion::class);
        $clientRecipe2->expects($this->once())
                      ->method('getName')
                      ->willReturn('def');

        $expectedResult = [
            'abc' => $clientRecipe1,
            'def' => $clientRecipe2,
        ];

        /* @var RecipeDataCollectionToGenericEntityWithRecipesMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeDataCollectionToGenericEntityWithRecipesMapper::class)
                       ->setMethods(['mapDatabaseRecipe'])
                       ->setConstructorArgs([$this->recipeService])
                       ->getMock();
        $mapper->setMapperManager($this->mapperManager);
        $mapper->expects($this->exactly(2))
               ->method('mapDatabaseRecipe')
               ->withConsecutive(
                   [$this->identicalTo($databaseRecipe1)],
                   [$this->identicalTo($databaseRecipe2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $clientRecipe1,
                   $clientRecipe2
               );
        $this->injectProperty($mapper, 'databaseRecipes', $databaseRecipes);

        $result = $this->invokeMethod($mapper, 'mapNormalRecipes', $recipeDataCollection);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapExpensiveRecipes method.
     * @throws ReflectionException
     * @covers ::mapExpensiveRecipes
     */
    public function testMapExpensiveRecipes(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData1->expects($this->atLeastOnce())
                    ->method('getId')
                    ->willReturn(42);

        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        $recipeData2->expects($this->atLeastOnce())
                    ->method('getId')
                    ->willReturn(1337);

        /* @var RecipeData&MockObject $recipeData3 */
        $recipeData3 = $this->createMock(RecipeData::class);
        $recipeData3->expects($this->atLeastOnce())
                    ->method('getId')
                    ->willReturn(21);

        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);
        $recipeDataCollection->expects($this->once())
                             ->method('getValues')
                             ->willReturn([$recipeData1, $recipeData2, $recipeData3]);

        /* @var DatabaseRecipe&MockObject $databaseRecipe1 */
        $databaseRecipe1 = $this->createMock(DatabaseRecipe::class);
        /* @var DatabaseRecipe&MockObject $databaseRecipe2 */
        $databaseRecipe2 = $this->createMock(DatabaseRecipe::class);

        $databaseRecipes = [
            42 => $databaseRecipe1,
            21 => $databaseRecipe2,
        ];

        /* @var RecipeWithExpensiveVersion&MockObject $clientRecipe1 */
        $clientRecipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        /* @var RecipeWithExpensiveVersion&MockObject $clientRecipe2 */
        $clientRecipe2 = $this->createMock(RecipeWithExpensiveVersion::class);

        $recipes1 = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];
        $recipes2 = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];
        $recipes3 = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];

        /* @var RecipeDataCollectionToGenericEntityWithRecipesMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeDataCollectionToGenericEntityWithRecipesMapper::class)
                       ->setMethods(['mapDatabaseRecipe', 'addExpensiveRecipe'])
                       ->setConstructorArgs([$this->recipeService])
                       ->getMock();
        $mapper->setMapperManager($this->mapperManager);
        $mapper->expects($this->exactly(2))
               ->method('mapDatabaseRecipe')
               ->withConsecutive(
                   [$this->identicalTo($databaseRecipe1)],
                   [$this->identicalTo($databaseRecipe2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $clientRecipe1,
                   $clientRecipe2
               );
        $mapper->expects($this->exactly(2))
               ->method('addExpensiveRecipe')
               ->withConsecutive(
                   [$this->identicalTo($recipes1), $this->identicalTo($clientRecipe1)],
                   [$this->identicalTo($recipes2), $this->identicalTo($clientRecipe2)]
               )
               ->willReturnOnConsecutiveCalls(
                   $recipes2,
                   $recipes3
               );
        $this->injectProperty($mapper, 'databaseRecipes', $databaseRecipes);

        $result = $this->invokeMethod($mapper, 'mapExpensiveRecipes', $recipes1, $recipeDataCollection);

        $this->assertEquals($recipes3, $result);
    }

    /**
     * Tests the addExpensiveRecipe method with a match.
     * @throws ReflectionException
     * @covers ::addExpensiveRecipe
     */
    public function testAddExpensiveRecipeWithMatch(): void
    {
        /* @var RecipeWithExpensiveVersion&MockObject $expensiveRecipe */
        $expensiveRecipe = $this->createMock(RecipeWithExpensiveVersion::class);
        $expensiveRecipe->expects($this->atLeastOnce())
                        ->method('getName')
                        ->willReturn('abc');

        /* @var RecipeWithExpensiveVersion&MockObject $recipe1 */
        $recipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        $recipe1->expects($this->once())
                ->method('setExpensiveVersion')
                ->with($this->identicalTo($expensiveRecipe));

        /* @var RecipeWithExpensiveVersion&MockObject $recipe2 */
        $recipe2 = $this->createMock(RecipeWithExpensiveVersion::class);
        $recipe2->expects($this->never())
                ->method('setExpensiveVersion');

        $recipes = [
            'abc' => $recipe1,
            'def' => $recipe2,
        ];

        $mapper = new RecipeDataCollectionToGenericEntityWithRecipesMapper($this->recipeService);
        $mapper->setMapperManager($this->mapperManager);

        $result = $this->invokeMethod($mapper, 'addExpensiveRecipe', $recipes, $expensiveRecipe);

        $this->assertEquals($recipes, $result);
    }

    /**
     * Tests the addExpensiveRecipe method without a match.
     * @throws ReflectionException
     * @covers ::addExpensiveRecipe
     */
    public function testAddExpensiveRecipeWithoutMatch(): void
    {
        /* @var RecipeWithExpensiveVersion&MockObject $expensiveRecipe */
        $expensiveRecipe = $this->createMock(RecipeWithExpensiveVersion::class);
        $expensiveRecipe->expects($this->atLeastOnce())
                        ->method('getName')
                        ->willReturn('abc');

        /* @var RecipeWithExpensiveVersion&MockObject $recipe1 */
        $recipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        $recipe1->expects($this->never())
                ->method('setExpensiveVersion');

        /* @var RecipeWithExpensiveVersion&MockObject $recipe2 */
        $recipe2 = $this->createMock(RecipeWithExpensiveVersion::class);
        $recipe2->expects($this->never())
                ->method('setExpensiveVersion');

        $recipes = [
            'foo' => $recipe1,
            'bar' => $recipe2,
        ];
        $expectedRecipes = [
            'foo' => $recipe1,
            'bar' => $recipe2,
            'abc' => $expensiveRecipe,
        ];

        $mapper = new RecipeDataCollectionToGenericEntityWithRecipesMapper($this->recipeService);
        $mapper->setMapperManager($this->mapperManager);

        $result = $this->invokeMethod($mapper, 'addExpensiveRecipe', $recipes, $expensiveRecipe);

        $this->assertEquals($expectedRecipes, $result);
    }

    /**
     * Tests the mapDatabaseRecipe method.
     * @throws ReflectionException
     * @covers ::mapDatabaseRecipe
     */
    public function testMapDatabaseRecipe(): void
    {
        /* @var DatabaseRecipe&MockObject $databaseRecipe */
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($databaseRecipe),
                                $this->isInstanceOf(RecipeWithExpensiveVersion::class)
                            );

        $mapper = new RecipeDataCollectionToGenericEntityWithRecipesMapper($this->recipeService);
        $mapper->setMapperManager($this->mapperManager);

        $this->invokeMethod($mapper, 'mapDatabaseRecipe', $databaseRecipe);
    }
}
