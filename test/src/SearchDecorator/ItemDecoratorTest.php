<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\SearchDecorator;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Search\Entity\Result\ItemResult;
use FactorioItemBrowser\Api\Search\Entity\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\SearchDecorator\ItemDecorator;
use FactorioItemBrowser\Api\Server\SearchDecorator\RecipeDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
     * The mocked item repository.
     * @var ItemRepository&MockObject
     */
    protected $itemRepository;

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
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->itemRepository = $this->createMock(ItemRepository::class);
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
        $decorator = new ItemDecorator($this->itemRepository, $this->mapperManager, $this->recipeDecorator);

        $this->assertSame($this->itemRepository, $this->extractProperty($decorator, 'itemRepository'));
        $this->assertSame($this->mapperManager, $this->extractProperty($decorator, 'mapperManager'));
        $this->assertSame($this->recipeDecorator, $this->extractProperty($decorator, 'recipeDecorator'));
    }

    /**
     * Tests the getSupportedResultClass method.
     * @covers ::getSupportedResultClass
     */
    public function testGetSupportedResultClass(): void
    {
        $decorator = new ItemDecorator($this->itemRepository, $this->mapperManager, $this->recipeDecorator);
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

        $decorator = new ItemDecorator($this->itemRepository, $this->mapperManager, $this->recipeDecorator);
        $this->injectProperty($decorator, 'itemIds', [$this->createMock(UuidInterface::class)]);
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
        /* @var UuidInterface&MockObject $itemId */
        $itemId = $this->createMock(UuidInterface::class);
        /* @var UuidInterface&MockObject $existingItemId */
        $existingItemId = $this->createMock(UuidInterface::class);

        $itemIds = [$existingItemId];
        $expectedItemIds = [$existingItemId, $itemId];

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
                          ->onlyMethods(['getRecipesFromItem'])
                          ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeDecorator])
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
        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');

        $itemIds = [$id1, $id2, $id1, null];
        $expectedItemIds = [$id1, $id2];

        /* @var DatabaseItem&MockObject $item1 */
        $item1 = $this->createMock(DatabaseItem::class);
        $item1->expects($this->any())
              ->method('getId')
              ->willReturn($id1);

        /* @var DatabaseItem&MockObject $item2 */
        $item2 = $this->createMock(DatabaseItem::class);
        $item2->expects($this->any())
              ->method('getId')
              ->willReturn($id2);

        $items = [$item1, $item2];
        $expectedItems = [
            $id1->toString() => $item1,
            $id2->toString() => $item2,
        ];

        $this->itemRepository->expects($this->once())
                             ->method('findByIds')
                             ->with($this->equalTo($expectedItemIds))
                             ->willReturn($items);

        $decorator = new ItemDecorator($this->itemRepository, $this->mapperManager, $this->recipeDecorator);
        $this->injectProperty($decorator, 'itemIds', $itemIds);

        $decorator->prepare();

        $this->assertEquals($expectedItems, $this->extractProperty($decorator, 'items'));
    }

    /**
     * Tests the decorate method.
     * @throws MapperException
     * @throws ReflectionException
     * @covers ::decorate
     */
    public function testDecorate(): void
    {
        $itemId = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');

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

        $items = [
            $itemId->toString() => $item
        ];
        $recipeResults = [$recipeResult1, $recipeResult2, $recipeResult3];
        $allRecipeResults = [$recipeResult1, $recipeResult2, $recipeResult3, $this->createMock(RecipeResult::class)];

        /* @var ItemResult&MockObject $itemResult */
        $itemResult = $this->createMock(ItemResult::class);
        $itemResult->expects($this->any())
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
                          ->onlyMethods(['createEntityForItem', 'getRecipesFromItem'])
                          ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeDecorator])
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
        /* @var UuidInterface&MockObject $itemId */
        $itemId = $this->createMock(UuidInterface::class);
        $items = [];

        /* @var ItemResult&MockObject $itemResult */
        $itemResult = $this->createMock(ItemResult::class);
        $itemResult->expects($this->any())
                   ->method('getId')
                   ->willReturn($itemId);

        $this->recipeDecorator->expects($this->never())
                              ->method('decorateRecipe');

        /* @var ItemDecorator&MockObject $decorator */
        $decorator = $this->getMockBuilder(ItemDecorator::class)
                          ->onlyMethods(['createEntityForItem', 'getRecipesFromItem'])
                          ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeDecorator])
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
     * Tests the decorate method.
     * @throws MapperException
     * @covers ::decorate
     */
    public function testDecorateWithoutItemId(): void
    {
        /* @var ItemResult&MockObject $itemResult */
        $itemResult = $this->createMock(ItemResult::class);
        $itemResult->expects($this->any())
                   ->method('getId')
                   ->willReturn(null);

        $this->recipeDecorator->expects($this->never())
                              ->method('decorateRecipe');

        $decorator = new ItemDecorator($this->itemRepository, $this->mapperManager, $this->recipeDecorator);
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

        $decorator = new ItemDecorator($this->itemRepository, $this->mapperManager, $this->recipeDecorator);
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

        $decorator = new ItemDecorator($this->itemRepository, $this->mapperManager, $this->recipeDecorator);
        $this->injectProperty($decorator, 'numberOfRecipesPerResult', $numberOfRecipesPerResult);

        $result = $this->invokeMethod($decorator, 'getRecipesFromItem', $itemResult);

        $this->assertEquals($expectedResult, $result);
    }
}
