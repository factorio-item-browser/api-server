<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\SearchDecorator;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Search\Entity\Result\ItemResult;
use FactorioItemBrowser\Api\Search\Entity\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\SearchDecorator\ItemDecorator;
use FactorioItemBrowser\Api\Server\SearchDecorator\RecipeDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemDecorator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\SearchDecorator\ItemDecorator
 */
class ItemDecoratorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked item service.
     * @var ItemService&MockObject
     */
    protected $itemService;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * The mocked recipe decorator.
     * @var RecipeDecorator&MockObject
     */
    protected $recipeDecorator;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->itemService = $this->createMock(ItemService::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeDecorator = $this->createMock(RecipeDecorator::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $decorator = new ItemDecorator($this->itemService, $this->mapperManager, $this->recipeDecorator);

        $this->assertSame($this->itemService, $this->extractProperty($decorator, 'itemService'));
        $this->assertSame($this->mapperManager, $this->extractProperty($decorator, 'mapperManager'));
        $this->assertSame($this->recipeDecorator, $this->extractProperty($decorator, 'recipeDecorator'));
    }

    /**
     * Tests the getSupportedResultClass method.
     * @covers ::getSupportedResultClass
     */
    public function testGetSupportedResultClass(): void
    {
        $decorator = new ItemDecorator($this->itemService, $this->mapperManager, $this->recipeDecorator);
        $result = $decorator->getSupportedResultClass();

        $this->assertSame(ItemResult::class, $result);
    }

    /**
     * Tests the initialize method.
     * @throws ReflectionException
     * @covers ::initialize
     */
    public function testInitialize(): void
    {
        $numberOfRecipesPerResult = 42;

        $decorator = new ItemDecorator($this->itemService, $this->mapperManager, $this->recipeDecorator);
        $this->injectProperty($decorator, 'itemIds', [1337]);
        $this->injectProperty($decorator, 'items', [$this->createMock(ItemResult::class)]);

        $decorator->initialize($numberOfRecipesPerResult);

        $this->assertSame($numberOfRecipesPerResult, $this->extractProperty($decorator, 'numberOfRecipesPerResult'));
        $this->assertSame([], $this->extractProperty($decorator, 'itemIds'));
        $this->assertSame([], $this->extractProperty($decorator, 'items'));
    }

    /**
     * Tests the announce method.
     * @throws ReflectionException
     * @covers ::announce
     */
    public function testAnnounce(): void
    {
        $itemIds = [1337];
        $expectedItemIds = [1337, 42];
        $itemId = 42;

        /* @var RecipeResult&MockObject $recipe1 */
        $recipe1 = $this->createMock(RecipeResult::class);
        /* @var RecipeResult&MockObject $recipe2 */
        $recipe2 = $this->createMock(RecipeResult::class);

        $recipes = [$recipe1, $recipe2];

        /* @var ItemResult&MockObject $itemResult */
        $itemResult = $this->createMock(ItemResult::class);
        $itemResult->expects($this->once())
                   ->method('getId')
                   ->willReturn($itemId);

        $this->recipeDecorator->expects($this->exactly(2))
                              ->method('announce')
                              ->withConsecutive(
                                  [$this->identicalTo($recipe1)],
                                  [$this->identicalTo($recipe2)]
                              );

        /* @var ItemDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(ItemDecorator::class)
                          ->setMethods(['getRecipesFromItem'])
                          ->setConstructorArgs([$this->itemService, $this->mapperManager, $this->recipeDecorator])
                          ->getMock();
        $decorator->expects($this->once())
                  ->method('getRecipesFromItem')
                  ->with($this->identicalTo($itemResult))
                  ->willReturn($recipes);
        $this->injectProperty($decorator, 'itemIds', $itemIds);

        $decorator->announce($itemResult);

        $this->assertEquals($expectedItemIds, $this->extractProperty($decorator, 'itemIds'));
    }

    /**
     * Tests the prepare method.
     * @throws ReflectionException
     * @covers ::prepare
     */
    public function testPrepare(): void
    {
        $itemIds = [42, 1337, 42, 0];
        $expectedItemIds = [42, 1337];

        $items = [
            $this->createMock(DatabaseItem::class),
            $this->createMock(DatabaseItem::class),
        ];

        $this->itemService->expects($this->once())
                          ->method('getByIds')
                          ->with($this->identicalTo($expectedItemIds))
                          ->willReturn($items);

        $decorator = new ItemDecorator($this->itemService, $this->mapperManager, $this->recipeDecorator);
        $this->injectProperty($decorator, 'itemIds', $itemIds);

        $decorator->prepare();

        $this->assertEquals($items, $this->extractProperty($decorator, 'items'));
    }

    /**
     * Tests the decorate method.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorate
     */
    public function testDecorate(): void
    {
        $itemId = 42;

        /* @var DatabaseItem&MockObject $item */
        $item = $this->createMock(DatabaseItem::class);
        /* @var RecipeResult&MockObject $recipeResult1 */
        $recipeResult1 = $this->createMock(RecipeResult::class);
        /* @var RecipeResult&MockObject $recipeResult2 */
        $recipeResult2 = $this->createMock(RecipeResult::class);
        /* @var RecipeResult&MockObject $recipeResult3 */
        $recipeResult3 = $this->createMock(RecipeResult::class);
        /* @var RecipeWithExpensiveVersion&MockObject $recipe1 */
        $recipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        /* @var RecipeWithExpensiveVersion&MockObject $recipe2 */
        $recipe2 = $this->createMock(RecipeWithExpensiveVersion::class);

        $items = [42 => $item];
        $recipeResults = [$recipeResult1, $recipeResult2, $recipeResult3];
        $allRecipeResults = [$recipeResult1, $recipeResult2, $recipeResult3, $this->createMock(RecipeResult::class)];

        /* @var ItemResult&MockObject $itemResult */
        $itemResult = $this->createMock(ItemResult::class);
        $itemResult->expects($this->once())
                   ->method('getId')
                   ->willReturn($itemId);
        $itemResult->expects($this->once())
                   ->method('getRecipes')
                   ->willReturn($allRecipeResults);


        /* @var GenericEntityWithRecipes&MockObject $entity */
        $entity = $this->createMock(GenericEntityWithRecipes::class);
        $entity->expects($this->exactly(2))
               ->method('addRecipe')
               ->withConsecutive(
                   [$this->identicalTo($recipe1)],
                   [$this->identicalTo($recipe2)]
               );
        $entity->expects($this->once())
               ->method('setTotalNumberOfRecipes')
               ->with($this->identicalTo(4));

        $this->recipeDecorator->expects($this->exactly(3))
                              ->method('decorateRecipe')
                              ->withConsecutive(
                                  [$this->identicalTo($recipeResult1)],
                                  [$this->identicalTo($recipeResult2)],
                                  [$this->identicalTo($recipeResult3)]
                              )
                              ->willReturnOnConsecutiveCalls(
                                  $recipe1,
                                  null,
                                  $recipe2
                              );

        /* @var ItemDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(ItemDecorator::class)
                          ->setMethods(['createEntityForItem', 'getRecipesFromItem'])
                          ->setConstructorArgs([$this->itemService, $this->mapperManager, $this->recipeDecorator])
                          ->getMock();
        $decorator->expects($this->once())
                  ->method('createEntityForItem')
                  ->with($this->identicalTo($item))
                  ->willReturn($entity);
        $decorator->expects($this->once())
                  ->method('getRecipesFromItem')
                  ->with($this->identicalTo($itemResult))
                  ->willReturn($recipeResults);
        $this->injectProperty($decorator, 'items', $items);

        $result = $decorator->decorate($itemResult);

        $this->assertSame($entity, $result);
    }

    /**
     * Tests the decorate method without having the item to decorate.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorate
     */
    public function testDecorateWithoutItem(): void
    {
        $itemId = 42;
        $items = [];

        /* @var ItemResult&MockObject $itemResult */
        $itemResult = $this->createMock(ItemResult::class);
        $itemResult->expects($this->once())
                   ->method('getId')
                   ->willReturn($itemId);

        $this->recipeDecorator->expects($this->never())
                              ->method('decorateRecipe');

        /* @var ItemDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(ItemDecorator::class)
                          ->setMethods(['createEntityForItem', 'getRecipesFromItem'])
                          ->setConstructorArgs([$this->itemService, $this->mapperManager, $this->recipeDecorator])
                          ->getMock();
        $decorator->expects($this->never())
                  ->method('createEntityForItem');
        $decorator->expects($this->never())
                  ->method('getRecipesFromItem');
        $this->injectProperty($decorator, 'items', $items);

        $result = $decorator->decorate($itemResult);

        $this->assertNull($result);
    }

    /**
     * Tests the createEntityForItem method.
     * @throws ReflectionException
     * @covers ::createEntityForItem
     */
    public function testCreateEntityForItem(): void
    {
        /* @var DatabaseItem&MockObject $databaseItem */
        $databaseItem = $this->createMock(DatabaseItem::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($databaseItem),
                                $this->isInstanceOf(GenericEntityWithRecipes::class)
                            );

        $decorator = new ItemDecorator($this->itemService, $this->mapperManager, $this->recipeDecorator);
        $this->invokeMethod($decorator, 'createEntityForItem', $databaseItem);
    }

    /**
     * Tests the getRecipesFromItem method.
     * @throws ReflectionException
     * @covers ::getRecipesFromItem
     */
    public function testGetRecipesFromItem(): void
    {
        $numberOfRecipesPerResult = 2;

        /* @var RecipeResult&MockObject $recipe1 */
        $recipe1 = $this->createMock(RecipeResult::class);
        /* @var RecipeResult&MockObject $recipe2 */
        $recipe2 = $this->createMock(RecipeResult::class);

        $recipes = [
            $recipe1,
            $recipe2,
            $this->createMock(RecipeResult::class),
            $this->createMock(RecipeResult::class),
        ];
        $expectedResult = [$recipe1, $recipe2];

        /* @var ItemResult&MockObject $itemResult */
        $itemResult = $this->createMock(ItemResult::class);
        $itemResult->expects($this->once())
                   ->method('getRecipes')
                   ->willReturn($recipes);

        $decorator = new ItemDecorator($this->itemService, $this->mapperManager, $this->recipeDecorator);
        $this->injectProperty($decorator, 'numberOfRecipesPerResult', $numberOfRecipesPerResult);

        $result = $this->invokeMethod($decorator, 'getRecipesFromItem', $itemResult);

        $this->assertEquals($expectedResult, $result);
    }
}