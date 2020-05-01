<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Item\ItemListRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemListHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the ItemListHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\ItemListHandler
 */
class ItemListHandlerTest extends TestCase
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
     * The mocked recipe service.
     * @var RecipeService&MockObject
     */
    protected $recipeService;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->itemRepository = $this->createMock(ItemRepository::class);
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
        $handler = new ItemListHandler($this->itemRepository, $this->mapperManager, $this->recipeService);

        $this->assertSame($this->itemRepository, $this->extractProperty($handler, 'itemRepository'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
        $this->assertSame($this->recipeService, $this->extractProperty($handler, 'recipeService'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = ItemListRequest::class;

        $handler = new ItemListHandler($this->itemRepository, $this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'getExpectedRequestClass');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the handleRequest method.
     * @throws ReflectionException
     * @covers ::handleRequest
     */
    public function testHandleRequest(): void
    {
        $numberOfResults = 2;
        $indexOfFirstResult = 1;
        $numberOfRecipesPerResult = 42;

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var ItemListResponse&MockObject $response */
        $response = $this->createMock(ItemListResponse::class);

        /* @var Item&MockObject $item1 */
        $item1 = $this->createMock(Item::class);
        /* @var Item&MockObject $item2 */
        $item2 = $this->createMock(Item::class);

        $items = [
            $this->createMock(Item::class),
            $item1,
            $item2,
            $this->createMock(Item::class),
        ];
        $limitedItems = [
            $item1,
            $item2,
        ];
        $recipeIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];
        $mappedItems = [
            $this->createMock(GenericEntityWithRecipes::class),
            $this->createMock(GenericEntityWithRecipes::class),
        ];

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        /* @var ItemListRequest&MockObject $request */
        $request = $this->createMock(ItemListRequest::class);
        $request->expects($this->once())
                ->method('getNumberOfResults')
                ->willReturn($numberOfResults);
        $request->expects($this->once())
                ->method('getIndexOfFirstResult')
                ->willReturn($indexOfFirstResult);
        $request->expects($this->once())
                ->method('getNumberOfRecipesPerResult')
                ->willReturn($numberOfRecipesPerResult);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getAllIds')
                   ->willReturn($recipeIds);

        $this->itemRepository->expects($this->once())
                             ->method('findAll')
                             ->with($this->identicalTo($combinationId))
                             ->willReturn($items);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithProducts')
                            ->with($this->identicalTo($limitedItems), $this->identicalTo($authorizationToken))
                            ->willReturn($recipeData);
        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($recipeIds);

        /* @var ItemListHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ItemListHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'mapItems', 'createResponse'])
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('mapItems')
                ->with(
                    $this->identicalTo($limitedItems),
                    $this->identicalTo($recipeData),
                    $this->identicalTo($numberOfRecipesPerResult)
                )
                ->willReturn($mappedItems);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->identicalTo($mappedItems), $this->identicalTo(4))
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the mapItems method.
     * @throws ReflectionException
     * @covers ::mapItems
     */
    public function testMapItems(): void
    {
        $numberOfRecipes = 42;

        /* @var UuidInterface&MockObject $itemId1 */
        $itemId1 = $this->createMock(UuidInterface::class);
        /* @var UuidInterface&MockObject $itemId2 */
        $itemId2 = $this->createMock(UuidInterface::class);
        /* @var GenericEntityWithRecipes&MockObject $mappedItem1 */
        $mappedItem1 = $this->createMock(GenericEntityWithRecipes::class);
        /* @var GenericEntityWithRecipes&MockObject $mappedItem2 */
        $mappedItem2 = $this->createMock(GenericEntityWithRecipes::class);
        /* @var RecipeDataCollection&MockObject $filteredRecipeData1 */
        $filteredRecipeData1 = $this->createMock(RecipeDataCollection::class);
        /* @var RecipeDataCollection&MockObject $filteredRecipeData2 */
        $filteredRecipeData2 = $this->createMock(RecipeDataCollection::class);

        /* @var Item&MockObject $item1 */
        $item1 = $this->createMock(Item::class);
        $item1->expects($this->once())
              ->method('getId')
              ->willReturn($itemId1);

        /* @var Item&MockObject $item2 */
        $item2 = $this->createMock(Item::class);
        $item2->expects($this->once())
              ->method('getId')
              ->willReturn($itemId2);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->exactly(2))
                   ->method('filterItemId')
                   ->withConsecutive(
                       [$this->identicalTo($itemId1)],
                       [$this->identicalTo($itemId2)]
                   )
                   ->willReturnOnConsecutiveCalls(
                       $filteredRecipeData1,
                       $filteredRecipeData2
                   );

        $items = [$item1, $item2];
        $expectedResult = [$mappedItem1, $mappedItem2];

        /* @var ItemListHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ItemListHandler::class)
                        ->onlyMethods(['createItem'])
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('createItem')
                ->withConsecutive(
                    [
                        $this->identicalTo($item1),
                        $this->identicalTo($filteredRecipeData1),
                        $this->identicalTo($numberOfRecipes),
                    ],
                    [
                        $this->identicalTo($item2),
                        $this->identicalTo($filteredRecipeData2),
                        $this->identicalTo($numberOfRecipes),
                    ]
                )
                ->willReturnOnConsecutiveCalls(
                    $mappedItem1,
                    $mappedItem2
                );
        $result = $this->invokeMethod($handler, 'mapItems', $items, $recipeData, $numberOfRecipes);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the createItem method.
     * @throws ReflectionException
     * @covers ::createItem
     */
    public function testCreateItem(): void
    {
        $numberOfRecipes = 42;
        $countNames = 21;

        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        /* @var RecipeDataCollection&MockObject $limitedRecipeData */
        $limitedRecipeData = $this->createMock(RecipeDataCollection::class);

        $expectedResult = new GenericEntityWithRecipes();
        $expectedResult->setTotalNumberOfRecipes($countNames);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('limitNames')
                   ->with($this->identicalTo($numberOfRecipes), $this->identicalTo(0))
                   ->willReturn($limitedRecipeData);
        $recipeData->expects($this->once())
                   ->method('countNames')
                   ->willReturn($countNames);

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($item), $this->isInstanceOf(GenericEntityWithRecipes::class)],
                                [
                                    $this->identicalTo($limitedRecipeData),
                                    $this->isInstanceOf(GenericEntityWithRecipes::class)
                                ]
                            );

        $handler = new ItemListHandler($this->itemRepository, $this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'createItem', $item, $recipeData, $numberOfRecipes);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        $items = [
            $this->createMock(GenericEntityWithRecipes::class),
            $this->createMock(GenericEntityWithRecipes::class),
        ];
        $numberOfItems = 42;

        $expectedResult = new ItemListResponse();
        $expectedResult->setItems($items)
                       ->setTotalNumberOfResults($numberOfItems);

        $handler = new ItemListHandler($this->itemRepository, $this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'createResponse', $items, $numberOfItems);

        $this->assertEquals($expectedResult, $result);
    }
}
