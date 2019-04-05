<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Item\ItemRandomRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemRandomResponse;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemRandomHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemRandomHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\ItemRandomHandler
 */
class ItemRandomHandlerTest extends TestCase
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
     * @throws ReflectionException
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
        $handler = new ItemRandomHandler($this->itemRepository, $this->mapperManager, $this->recipeService);

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
        $expectedResult = ItemRandomRequest::class;

        $handler = new ItemRandomHandler($this->itemRepository, $this->mapperManager, $this->recipeService);
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
        $enabledModCombinationIds = [42, 1337];
        $numberOfResults = 21;
        $numberOfRecipesPerResult = 7331;
        $recipeIds = [13, 37];

        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];

        /* @var ItemRandomResponse&MockObject $response */
        $response = $this->createMock(ItemRandomResponse::class);

        /* @var ItemRandomRequest&MockObject $request */
        $request = $this->createMock(ItemRandomRequest::class);
        $request->expects($this->once())
                ->method('getNumberOfResults')
                ->willReturn($numberOfResults);
        $request->expects($this->once())
                ->method('getNumberOfRecipesPerResult')
                ->willReturn($numberOfRecipesPerResult);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getAllIds')
                   ->willReturn($recipeIds);

        $this->itemRepository->expects($this->once())
                             ->method('findRandom')
                             ->with($this->identicalTo($numberOfResults), $this->identicalTo($enabledModCombinationIds))
                             ->willReturn($items);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithProducts')
                            ->with($this->identicalTo($items), $this->identicalTo($authorizationToken))
                            ->willReturn($recipeData);

        /* @var ItemRandomHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ItemRandomHandler::class)
                        ->setMethods(['getAuthorizationToken', 'createResponse'])
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with(
                    $this->identicalTo($items),
                    $this->identicalTo($recipeData),
                    $this->identicalTo($numberOfRecipesPerResult)
                )
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        $numberOfRecipesPerResult = 42;

        /* @var RecipeDataCollection&MockObject $filteredRecipeData1 */
        $filteredRecipeData1 = $this->createMock(RecipeDataCollection::class);
        /* @var RecipeDataCollection&MockObject $filteredRecipeData2 */
        $filteredRecipeData2 = $this->createMock(RecipeDataCollection::class);
        /* @var GenericEntityWithRecipes&MockObject $clientItem1 */
        $clientItem1 = $this->createMock(GenericEntityWithRecipes::class);
        /* @var GenericEntityWithRecipes&MockObject $clientItem2 */
        $clientItem2 = $this->createMock(GenericEntityWithRecipes::class);

        $expectedResult = new ItemRandomResponse();
        $expectedResult->addItem($clientItem1)
                       ->addItem($clientItem2);

        /* @var Item&MockObject $databaseItem1 */
        $databaseItem1 = $this->createMock(Item::class);
        $databaseItem1->expects($this->once())
                      ->method('getId')
                      ->willReturn(21);

        /* @var Item&MockObject $databaseItem2 */
        $databaseItem2 = $this->createMock(Item::class);
        $databaseItem2->expects($this->once())
                      ->method('getId')
                      ->willReturn(1337);

        $items = [
            $databaseItem1,
            $databaseItem2,
        ];

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->exactly(2))
                   ->method('filterItemId')
                   ->withConsecutive(
                       [$this->identicalTo(21)],
                       [$this->identicalTo(1337)]
                   )
                   ->willReturnOnConsecutiveCalls(
                       $filteredRecipeData1,
                       $filteredRecipeData2
                   );

        /* @var ItemRandomHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ItemRandomHandler::class)
                        ->setMethods(['createItem'])
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('createItem')
                ->withConsecutive(
                    [
                        $this->identicalTo($databaseItem1),
                        $this->identicalTo($filteredRecipeData1),
                        $this->identicalTo($numberOfRecipesPerResult)
                    ],
                    [
                        $this->identicalTo($databaseItem2),
                        $this->identicalTo($filteredRecipeData2),
                        $this->identicalTo($numberOfRecipesPerResult)
                    ]
                )
                ->willReturnOnConsecutiveCalls(
                    $clientItem1,
                    $clientItem2
                );

        $result = $this->invokeMethod($handler, 'createResponse', $items, $recipeData, $numberOfRecipesPerResult);

        $this->assertEquals($expectedResult, $result);
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

        $handler = new ItemRandomHandler($this->itemRepository, $this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'createItem', $item, $recipeData, $numberOfRecipes);

        $this->assertEquals($expectedResult, $result);
    }
}
