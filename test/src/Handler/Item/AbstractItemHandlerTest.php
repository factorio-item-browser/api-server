<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Handler\Item\AbstractItemHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractItemHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Handler\Item\AbstractItemHandler
 */
class AbstractItemHandlerTest extends TestCase
{
    use ReflectionTrait;

    /** @var ItemRepository&MockObject */
    private ItemRepository $itemRepository;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeService&MockObject */
    private RecipeService $recipeService;

    protected function setUp(): void
    {
        $this->itemRepository = $this->createMock(ItemRepository::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeService = $this->createMock(RecipeService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return AbstractItemHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): AbstractItemHandler
    {
        return $this->getMockBuilder(AbstractItemHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->itemRepository,
                        $this->mapperManager,
                        $this->recipeService,
                    ])
                    ->getMockForAbstractClass();
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchItem(): void
    {
        $combinationId = $this->createMock(UuidInterface::class);
        $type = 'abc';
        $name = 'def';

        $expectedNamesByTypes = new NamesByTypes();
        $expectedNamesByTypes->addName('abc', 'def');

        $item = $this->createMock(Item::class);

        $this->itemRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with($this->identicalTo($combinationId), $this->equalTo($expectedNamesByTypes))
                             ->willReturn([$item]);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchItem', $combinationId, $type, $name);

        $this->assertSame($item, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchItemWithoutEntity(): void
    {
        $combinationId = $this->createMock(UuidInterface::class);
        $type = 'abc';
        $name = 'def';

        $expectedNamesByTypes = new NamesByTypes();
        $expectedNamesByTypes->addName('abc', 'def');

        $this->itemRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with($this->identicalTo($combinationId), $this->equalTo($expectedNamesByTypes))
                             ->willReturn([]);

        $this->expectException(EntityNotFoundException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'fetchItem', $combinationId, $type, $name);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapItems(): void
    {
        $combinationId = $this->createMock(UuidInterface::class);

        $item1 = new Item();
        $item1->setId(Uuid::fromString('114a2421-893a-4113-b67d-b4bc1eac2cd2'));
        $item2 = new Item();
        $item2->setId(Uuid::fromString('25907ab0-acbc-4941-8966-423758ec2440'));

        $items = [$item1, $item2];
        $itemIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];

        $mappedItem1 = $this->createMock(GenericEntityWithRecipes::class);
        $mappedItem2 = $this->createMock(GenericEntityWithRecipes::class);
        $expectedResult = [$mappedItem1, $mappedItem2];

        $itemRecipeData1 = $this->createMock(RecipeDataCollection::class);
        $itemRecipeData2 = $this->createMock(RecipeDataCollection::class);

        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getAllIds')
                   ->willReturn($itemIds);
        $recipeData->expects($this->exactly(2))
                   ->method('filterItemId')
                   ->withConsecutive(
                       [$this->equalTo(Uuid::fromString('114a2421-893a-4113-b67d-b4bc1eac2cd2'))],
                       [$this->equalTo(Uuid::fromString('25907ab0-acbc-4941-8966-423758ec2440'))],
                   )
                   ->willReturnOnConsecutiveCalls(
                       $itemRecipeData1,
                       $itemRecipeData2,
                   );

        $this->recipeService->expects($this->once())
                            ->method('getDataWithProducts')
                            ->with($this->identicalTo($combinationId), $this->identicalTo($items))
                            ->willReturn($recipeData);
        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo($itemIds));

        $instance = $this->createInstance(['createItem']);
        $instance->expects($this->exactly(2))
                 ->method('createItem')
                 ->withConsecutive(
                     [
                         $this->identicalTo($item1),
                         $this->identicalTo($itemRecipeData1),
                         $this->identicalTo(21),
                         $this->identicalTo(0),
                     ],
                     [
                         $this->identicalTo($item2),
                         $this->identicalTo($itemRecipeData2),
                         $this->identicalTo(21),
                         $this->identicalTo(0),
                     ],
                 )
                 ->willReturnOnConsecutiveCalls(
                     $mappedItem1,
                     $mappedItem2,
                 );

        $result = $this->invokeMethod($instance, 'mapItems', $combinationId, $items, 21);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapItemsWithoutRecipes(): void
    {
        $combinationId = $this->createMock(UuidInterface::class);
        $numberOfRecipesPerResult = 0;

        $item1 = $this->createMock(Item::class);
        $item2 = $this->createMock(Item::class);
        $mappedItem1 = $this->createMock(GenericEntity::class);
        $mappedItem2 = $this->createMock(GenericEntity::class);
        $items = [$item1, $item2];
        $expectedResult = [$mappedItem1, $mappedItem2];

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($item1), $this->isInstanceOf(GenericEntity::class)],
                                [$this->identicalTo($item2), $this->isInstanceOf(GenericEntity::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $mappedItem1,
                                $mappedItem2,
                            );
        $this->recipeService->expects($this->never())
                            ->method('getDataWithProducts');
        $this->recipeService->expects($this->never())
                            ->method('getDetailsByIds');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'mapItems', $combinationId, $items, $numberOfRecipesPerResult);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateItem(): void
    {
        $item = $this->createMock(Item::class);
        $limitedRecipeData = $this->createMock(RecipeDataCollection::class);

        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('limitNames')
                   ->with($this->identicalTo(21), $this->identicalTo(42))
                   ->willReturn($limitedRecipeData);
        $recipeData->expects($this->once())
                   ->method('countNames')
                   ->willReturn(1337);

        $expectedResult = new GenericEntityWithRecipes();
        $expectedResult->totalNumberOfRecipes = 1337;

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [
                                    $this->identicalTo($item),
                                    $this->isInstanceOf(GenericEntityWithRecipes::class),
                                ],
                                [
                                    $this->identicalTo($limitedRecipeData),
                                    $this->isInstanceOf(GenericEntityWithRecipes::class),
                                ],
                            );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createItem', $item, $recipeData, 21, 42);

        $this->assertEquals($expectedResult, $result);
    }
}
