<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\SearchDecorator;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
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
 * @covers \FactorioItemBrowser\Api\Server\SearchDecorator\ItemDecorator
 */
class ItemDecoratorTest extends TestCase
{
    use ReflectionTrait;

    /** @var ItemRepository&MockObject */
    private ItemRepository $itemRepository;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeDecorator&MockObject */
    private RecipeDecorator $recipeDecorator;

    protected function setUp(): void
    {
        $this->itemRepository = $this->createMock(ItemRepository::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeDecorator = $this->createMock(RecipeDecorator::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ItemDecorator&MockObject
     */
    private function createInstance(array $mockedMethods = []): ItemDecorator
    {
        return $this->getMockBuilder(ItemDecorator::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->itemRepository,
                        $this->mapperManager,
                        $this->recipeDecorator,
                    ])
                    ->getMock();
    }

    public function testGetSupportedResultClass(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(ItemResult::class, $instance->getSupportedResultClass());
    }

    /**
     * @throws ReflectionException
     */
    public function testInitialize(): void
    {
        $numberOfRecipesPerResult = 42;

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'announcedItemIds', [$this->createMock(UuidInterface::class)]);
        $this->injectProperty($instance, 'databaseItems', [$this->createMock(ItemResult::class)]);

        $instance->initialize($numberOfRecipesPerResult);

        $this->assertSame($numberOfRecipesPerResult, $this->extractProperty($instance, 'numberOfRecipesPerResult'));
        $this->assertSame([], $this->extractProperty($instance, 'announcedItemIds'));
        $this->assertSame([], $this->extractProperty($instance, 'databaseItems'));
    }

    /**
     * @throws ReflectionException
     */
    public function testAnnounce(): void
    {
        $announcedItemIds = [
            '04041eb4-cb94-4c47-8b17-90a5f5b28cae' => Uuid::fromString('04041eb4-cb94-4c47-8b17-90a5f5b28cae'),
        ];
        $expectedAnnouncedItemIds = [
            '04041eb4-cb94-4c47-8b17-90a5f5b28cae' => Uuid::fromString('04041eb4-cb94-4c47-8b17-90a5f5b28cae'),
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'),
        ];

        $recipe1 = $this->createMock(RecipeResult::class);
        $recipe2 = $this->createMock(RecipeResult::class);
        $recipe3 = $this->createMock(RecipeResult::class);

        $itemResult = new ItemResult();
        $itemResult->setId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'))
                   ->addRecipe($recipe1)
                   ->addRecipe($recipe2)
                   ->addRecipe($recipe3);

        $this->recipeDecorator->expects($this->exactly(2))
                              ->method('announce')
                              ->withConsecutive(
                                  [$this->identicalTo($recipe1)],
                                  [$this->identicalTo($recipe2)]
                              );

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'numberOfRecipesPerResult', 2)
             ->injectProperty($instance, 'announcedItemIds', $announcedItemIds);

        $instance->announce($itemResult);

        $this->assertEquals($expectedAnnouncedItemIds, $this->extractProperty($instance, 'announcedItemIds'));
    }

    /**
     * @throws ReflectionException
     */
    public function testPrepare(): void
    {
        $announcedItemIds = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'),
            '2a098339-f069-4941-a0f8-1f9518dc036b' => Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'),
        ];
        $expectedItemIds = [
            Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'),
            Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'),
        ];

        $item1 = new DatabaseItem();
        $item1->setId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'));
        $item2 = new DatabaseItem();
        $item2->setId(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'));

        $expectedDatabaseItems = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => $item1,
            '2a098339-f069-4941-a0f8-1f9518dc036b' => $item2,
        ];

        $this->itemRepository->expects($this->once())
                             ->method('findByIds')
                             ->with($this->equalTo($expectedItemIds))
                             ->willReturn([$item1, $item2]);

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'announcedItemIds', $announcedItemIds);

        $instance->prepare();

        $this->assertEquals($expectedDatabaseItems, $this->extractProperty($instance, 'databaseItems'));
    }
    /**
     * @throws ReflectionException
     */
    public function testDecorate(): void
    {
        $recipeResult1 = $this->createMock(RecipeResult::class);
        $recipeResult2 = $this->createMock(RecipeResult::class);
        $recipeResult3 = $this->createMock(RecipeResult::class);
        $recipeResult4 = $this->createMock(RecipeResult::class);
        $recipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        $recipe2 = $this->createMock(RecipeWithExpensiveVersion::class);

        $searchResult = new ItemResult();
        $searchResult->setId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'))
                     ->addRecipe($recipeResult1)
                     ->addRecipe($recipeResult2)
                     ->addRecipe($recipeResult3)
                     ->addRecipe($recipeResult4);

        $entity = new GenericEntityWithRecipes();
        $entity->name = 'abc';

        $expectedResult = new GenericEntityWithRecipes();
        $expectedResult->name = 'abc';
        $expectedResult->recipes = [$recipe1, $recipe2];
        $expectedResult->totalNumberOfRecipes = 4;

        $this->recipeDecorator->expects($this->exactly(3))
                              ->method('decorateRecipe')
                              ->withConsecutive(
                                  [$this->identicalTo($recipeResult1)],
                                  [$this->identicalTo($recipeResult2)],
                                  [$this->identicalTo($recipeResult3)],
                              )
                              ->willReturnOnConsecutiveCalls(
                                  $recipe1,
                                  null,
                                  $recipe2
                              );

        $instance = $this->createInstance(['mapItemWithId']);
        $instance->expects($this->once())
                 ->method('mapItemWithId')
                 ->with($this->equalTo(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b')))
                 ->willReturn($entity);
        $this->injectProperty($instance, 'numberOfRecipesPerResult', 3);

        $result = $instance->decorate($searchResult);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testDecorateWithoutEntity(): void
    {
        $searchResult = new ItemResult();
        $searchResult->setId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'));

        $this->recipeDecorator->expects($this->never())
                              ->method('decorateRecipe');

        $instance = $this->createInstance(['mapItemWithId']);
        $instance->expects($this->once())
                 ->method('mapItemWithId')
                 ->with($this->equalTo(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b')))
                 ->willReturn(null);
        $this->injectProperty($instance, 'numberOfRecipesPerResult', 3);

        $result = $instance->decorate($searchResult);

        $this->assertNull($result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapItemWithId(): void
    {
        $itemId = Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b');
        $databaseItem = $this->createMock(DatabaseItem::class);
        $mappedItem = $this->createMock(GenericEntityWithRecipes::class);

        $databaseItems = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => $databaseItem,
        ];

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($databaseItem),
                                $this->isInstanceOf(GenericEntityWithRecipes::class),
                            )
                            ->willReturn($mappedItem);

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'databaseItems', $databaseItems);

        $result = $this->invokeMethod($instance, 'mapItemWithId', $itemId);

        $this->assertSame($mappedItem, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapItemWithIdWithoutDatabaseItem(): void
    {
        $itemId = Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b');

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'mapItemWithId', $itemId);

        $this->assertNull($result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapItemWithIdWithoutId(): void
    {
        $this->mapperManager->expects($this->never())
                            ->method('map');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'mapItemWithId', null);

        $this->assertNull($result);
    }
}
