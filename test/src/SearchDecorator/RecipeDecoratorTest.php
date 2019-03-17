<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\SearchDecorator;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Search\Entity\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\SearchDecorator\RecipeDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeDecorator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\SearchDecorator\RecipeDecorator
 */
class RecipeDecoratorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * The mocked recipe service.
     * @var RecipeService&MockObject
     */
    protected $recipeService;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeService = $this->createMock(RecipeService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);

        $this->assertSame($this->mapperManager, $this->extractProperty($decorator, 'mapperManager'));
        $this->assertSame($this->recipeService, $this->extractProperty($decorator, 'recipeService'));
    }

    /**
     * Tests the getSupportedResultClass method.
     * @covers ::getSupportedResultClass
     */
    public function testGetSupportedResultClass(): void
    {
        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);
        $result = $decorator->getSupportedResultClass();

        $this->assertSame(RecipeResult::class, $result);
    }

    /**
     * Tests the initialize method.
     * @throws ReflectionException
     * @covers ::initialize
     */
    public function testInitialize(): void
    {
        $numberOfRecipesPerResult = 42;

        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);
        $this->injectProperty($decorator, 'recipeIds', [1337]);
        $this->injectProperty($decorator, 'recipes', [$this->createMock(RecipeResult::class)]);

        $decorator->initialize($numberOfRecipesPerResult);

        $this->assertSame([], $this->extractProperty($decorator, 'recipeIds'));
        $this->assertSame([], $this->extractProperty($decorator, 'recipes'));
    }

    /**
     * Tests the announce method.
     * @throws ReflectionException
     * @covers ::announce
     */
    public function testAnnounce(): void
    {
        $normalRecipeId = 42;
        $expensiveRecipeId = 21;

        $recipeIds = [1337];
        $expectedRecipeIds = [1337, 42, 21];

        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);
        $recipeResult->expects($this->once())
                     ->method('getNormalRecipeId')
                     ->willReturn($normalRecipeId);
        $recipeResult->expects($this->once())
                     ->method('getExpensiveRecipeId')
                     ->willReturn($expensiveRecipeId);

        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);
        $this->injectProperty($decorator, 'recipeIds', $recipeIds);

        $decorator->announce($recipeResult);

        $this->assertEquals($expectedRecipeIds, $this->extractProperty($decorator, 'recipeIds'));
    }

    /**
     * Tests the prepare method.
     * @throws ReflectionException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $recipeIds = [42, 1337, 42, 0];
        $expectedRecipeIds = [42, 1337];

        $recipes = [
            $this->createMock(DatabaseRecipe::class),
            $this->createMock(DatabaseRecipe::class),
        ];

        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo($expectedRecipeIds))
                            ->willReturn($recipes);

        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);
        $this->injectProperty($decorator, 'recipeIds', $recipeIds);

        $decorator->prepare();

        $this->assertEquals($recipes, $this->extractProperty($decorator, 'recipes'));
    }

    /**
     * Tests the decorate method.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorate
     */
    public function testDecorate(): void
    {
        $recipeId = 42;

        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);
        /* @var RecipeWithExpensiveVersion&MockObject $decoratedRecipe */
        $decoratedRecipe = $this->createMock(RecipeWithExpensiveVersion::class);
        /* @var DatabaseRecipe&MockObject $recipe */
        $recipe = $this->createMock(DatabaseRecipe::class);

        $recipes = [
            42 => $recipe,
        ];

        /* @var GenericEntityWithRecipes&MockObject $entity */
        $entity = $this->createMock(GenericEntityWithRecipes::class);
        $entity->expects($this->once())
               ->method('addRecipe')
               ->with($this->identicalTo($decoratedRecipe))
               ->willReturnSelf();
        $entity->expects($this->once())
               ->method('setTotalNumberOfRecipes')
               ->with($this->identicalTo(1));

        /* @var RecipeDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(RecipeDecorator::class)
                          ->setMethods(['getRecipeIdFromResult', 'createEntityForRecipe', 'decorateRecipe'])
                          ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                          ->getMock();
        $decorator->expects($this->once())
                  ->method('getRecipeIdFromResult')
                  ->with($this->identicalTo($recipeResult))
                  ->willReturn($recipeId);
        $decorator->expects($this->once())
                  ->method('createEntityForRecipe')
                  ->with($this->identicalTo($recipe))
                  ->willReturn($entity);
        $decorator->expects($this->once())
                  ->method('decorateRecipe')
                  ->with($recipeResult)
                  ->willReturn($decoratedRecipe);
        $this->injectProperty($decorator, 'recipes', $recipes);

        $result = $decorator->decorate($recipeResult);

        $this->assertSame($entity, $result);
    }

    /**
     * Tests the decorate method without being able to decorate the recipe (should never happen).
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorate
     */
    public function testDecorateWithoutDecoratedRecipe(): void
    {
        $recipeId = 42;

        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);
        /* @var DatabaseRecipe&MockObject $recipe */
        $recipe = $this->createMock(DatabaseRecipe::class);

        $recipes = [
            42 => $recipe,
        ];

        /* @var GenericEntityWithRecipes&MockObject $entity */
        $entity = $this->createMock(GenericEntityWithRecipes::class);
        $entity->expects($this->never())
               ->method('addRecipe');

        /* @var RecipeDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(RecipeDecorator::class)
                          ->setMethods(['getRecipeIdFromResult', 'createEntityForRecipe', 'decorateRecipe'])
                          ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                          ->getMock();
        $decorator->expects($this->once())
                  ->method('getRecipeIdFromResult')
                  ->with($this->identicalTo($recipeResult))
                  ->willReturn($recipeId);
        $decorator->expects($this->once())
                  ->method('createEntityForRecipe')
                  ->with($this->identicalTo($recipe))
                  ->willReturn($entity);
        $decorator->expects($this->once())
                  ->method('decorateRecipe')
                  ->with($recipeResult)
                  ->willReturn(null);
        $this->injectProperty($decorator, 'recipes', $recipes);

        $result = $decorator->decorate($recipeResult);

        $this->assertSame($entity, $result);
    }

    /**
     * Tests the decorate method without an actual recipe to decorate.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorate
     */
    public function testDecorateWithoutRecipe(): void
    {
        $recipeId = 42;

        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);

        $recipes = [];

        /* @var RecipeDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(RecipeDecorator::class)
                          ->setMethods(['getRecipeIdFromResult', 'createEntityForRecipe', 'decorateRecipe'])
                          ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                          ->getMock();
        $decorator->expects($this->once())
                  ->method('getRecipeIdFromResult')
                  ->with($this->identicalTo($recipeResult))
                  ->willReturn($recipeId);
        $decorator->expects($this->never())
                  ->method('createEntityForRecipe');
        $decorator->expects($this->never())
                  ->method('decorateRecipe');
        $this->injectProperty($decorator, 'recipes', $recipes);

        $result = $decorator->decorate($recipeResult);

        $this->assertNull($result);
    }

    /**
     * Tests the createEntityForRecipe method.
     * @throws ReflectionException
     * @covers ::createEntityForRecipe
     */
    public function testCreateEntityForRecipe(): void
    {
        /* @var DatabaseRecipe&MockObject $databaseRecipe */
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($databaseRecipe),
                                $this->isInstanceOf(GenericEntityWithRecipes::class)
                            );

        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);
        $this->invokeMethod($decorator, 'createEntityForRecipe', $databaseRecipe);
    }

    /**
     * Provides the data for the getRecipeIdFromResult test.
     * @return array
     */
    public function provideGetRecipeIdFromResult(): array
    {
        return [
            [42, 1337, 42],
            [0, 1337, 1337],
            [0, 0, 0],
        ];
    }

    /**
     * Tests the getRecipeIdFromResult method.
     * @param int $normalRecipeId
     * @param int $expensiveRecipeId
     * @param int $expectedReuslt
     * @throws ReflectionException
     * @covers ::getRecipeIdFromResult
     * @dataProvider provideGetRecipeIdFromResult
     */
    public function testGetRecipeIdFromResult(int $normalRecipeId, int $expensiveRecipeId, int $expectedReuslt): void
    {
        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);
        $recipeResult->expects($this->once())
                     ->method('getNormalRecipeId')
                     ->willReturn($normalRecipeId);
        $recipeResult->expects($this->any())
                     ->method('getExpensiveRecipeId')
                     ->willReturn($expensiveRecipeId);

        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($decorator, 'getRecipeIdFromResult', $recipeResult);

        $this->assertSame($expectedReuslt, $result);
    }

    /**
     * Tests the decorateRecipe method.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorateRecipe
     */
    public function testDecorateRecipe(): void
    {
        $normalRecipeId = 42;
        $expensiveRecipeId = 1337;

        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);
        $recipeResult->expects($this->once())
                     ->method('getNormalRecipeId')
                     ->willReturn($normalRecipeId);
        $recipeResult->expects($this->once())
                     ->method('getExpensiveRecipeId')
                     ->willReturn($expensiveRecipeId);

        /* @var RecipeWithExpensiveVersion&MockObject $expensiveRecipe */
        $expensiveRecipe = $this->createMock(RecipeWithExpensiveVersion::class);

        /* @var RecipeWithExpensiveVersion&MockObject $normalRecipe */
        $normalRecipe = $this->createMock(RecipeWithExpensiveVersion::class);
        $normalRecipe->expects($this->once())
                     ->method('setExpensiveVersion')
                     ->with($this->identicalTo($expensiveRecipe));

        /* @var RecipeDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(RecipeDecorator::class)
                          ->setMethods(['mapRecipeWithId'])
                          ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                          ->getMock();
        $decorator->expects($this->exactly(2))
                  ->method('mapRecipeWithId')
                  ->withConsecutive(
                      [$this->identicalTo($normalRecipeId)],
                      [$this->identicalTo($expensiveRecipeId)]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $normalRecipe,
                      $expensiveRecipe
                  );

        $result = $decorator->decorateRecipe($recipeResult);

        $this->assertSame($normalRecipe, $result);
    }

    /**
     * Tests the decorateRecipe method with only having a normal recipe.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorateRecipe
     */
    public function testDecorateRecipeWithNormalRecipe(): void
    {
        $normalRecipeId = 42;
        $expensiveRecipeId = 1337;

        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);
        $recipeResult->expects($this->once())
                     ->method('getNormalRecipeId')
                     ->willReturn($normalRecipeId);
        $recipeResult->expects($this->once())
                     ->method('getExpensiveRecipeId')
                     ->willReturn($expensiveRecipeId);

        /* @var RecipeWithExpensiveVersion&MockObject $normalRecipe */
        $normalRecipe = $this->createMock(RecipeWithExpensiveVersion::class);
        $normalRecipe->expects($this->never())
                     ->method('setExpensiveVersion');

        /* @var RecipeDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(RecipeDecorator::class)
                          ->setMethods(['mapRecipeWithId'])
                          ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                          ->getMock();
        $decorator->expects($this->exactly(2))
                  ->method('mapRecipeWithId')
                  ->withConsecutive(
                      [$this->identicalTo($normalRecipeId)],
                      [$this->identicalTo($expensiveRecipeId)]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $normalRecipe,
                      null
                  );

        $result = $decorator->decorateRecipe($recipeResult);

        $this->assertSame($normalRecipe, $result);
    }

    /**
     * Tests the decorateRecipe method with only having an expensive recipe.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorateRecipe
     */
    public function testDecorateRecipeWithExpensiveRecipe(): void
    {
        $normalRecipeId = 42;
        $expensiveRecipeId = 1337;

        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);
        $recipeResult->expects($this->once())
                     ->method('getNormalRecipeId')
                     ->willReturn($normalRecipeId);
        $recipeResult->expects($this->once())
                     ->method('getExpensiveRecipeId')
                     ->willReturn($expensiveRecipeId);

        /* @var RecipeWithExpensiveVersion&MockObject $expensiveRecipe */
        $expensiveRecipe = $this->createMock(RecipeWithExpensiveVersion::class);

        /* @var RecipeDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(RecipeDecorator::class)
                          ->setMethods(['mapRecipeWithId'])
                          ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                          ->getMock();
        $decorator->expects($this->exactly(2))
                  ->method('mapRecipeWithId')
                  ->withConsecutive(
                      [$this->identicalTo($normalRecipeId)],
                      [$this->identicalTo($expensiveRecipeId)]
                  )
                  ->willReturnOnConsecutiveCalls(
                      null,
                      $expensiveRecipe
                  );

        $result = $decorator->decorateRecipe($recipeResult);

        $this->assertSame($expensiveRecipe, $result);
    }

    /**
     * Tests the decorateRecipe method without any actual recipes.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorateRecipe
     */
    public function testDecorateRecipeWithoutRecipes(): void
    {
        $normalRecipeId = 42;
        $expensiveRecipeId = 1337;

        /* @var RecipeResult&MockObject $recipeResult */
        $recipeResult = $this->createMock(RecipeResult::class);
        $recipeResult->expects($this->once())
                     ->method('getNormalRecipeId')
                     ->willReturn($normalRecipeId);
        $recipeResult->expects($this->once())
                     ->method('getExpensiveRecipeId')
                     ->willReturn($expensiveRecipeId);

        /* @var RecipeDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(RecipeDecorator::class)
                          ->setMethods(['mapRecipeWithId'])
                          ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                          ->getMock();
        $decorator->expects($this->exactly(2))
                  ->method('mapRecipeWithId')
                  ->withConsecutive(
                      [$this->identicalTo($normalRecipeId)],
                      [$this->identicalTo($expensiveRecipeId)]
                  )
                  ->willReturnOnConsecutiveCalls(
                      null,
                      null
                  );

        $result = $decorator->decorateRecipe($recipeResult);

        $this->assertNull($result);
    }

    /**
     * Tests the mapRecipeWithId method.
     * @throws ReflectionException
     * @covers ::mapRecipeWithId
     */
    public function testMapRecipeWithId(): void
    {
        $recipeId = 42;

        /* @var DatabaseRecipe&MockObject $recipe */
        $recipe = $this->createMock(DatabaseRecipe::class);

        $recipes = [
            42 => $recipe,
        ];

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($recipe), $this->isInstanceOf(RecipeWithExpensiveVersion::class));

        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);
        $this->injectProperty($decorator, 'recipes', $recipes);

        $result = $this->invokeMethod($decorator, 'mapRecipeWithId', $recipeId);

        $this->assertInstanceOf(RecipeWithExpensiveVersion::class, $result);
    }

    /**
     * Tests the mapRecipeWithId method.
     * @throws ReflectionException
     * @covers ::mapRecipeWithId
     */
    public function testMapRecipeWithIdWithoutRecipe(): void
    {
        $recipeId = 42;
        $recipes = [];

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $decorator = new RecipeDecorator($this->mapperManager, $this->recipeService);
        $this->injectProperty($decorator, 'recipes', $recipes);

        $result = $this->invokeMethod($decorator, 'mapRecipeWithId', $recipeId);

        $this->assertNull($result);
    }
}
