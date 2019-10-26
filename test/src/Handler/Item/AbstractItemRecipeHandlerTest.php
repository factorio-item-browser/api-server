<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Item\ItemIngredientRequest;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Handler\Item\AbstractItemRecipeHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractItemRecipeHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\AbstractItemRecipeHandler
 */
class AbstractItemRecipeHandlerTest extends TestCase
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
        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();

        $this->assertSame($this->itemRepository, $this->extractProperty($handler, 'itemRepository'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
        $this->assertSame($this->recipeService, $this->extractProperty($handler, 'recipeService'));
    }

    /**
     * Tests the handleRequest method.
     * @throws ReflectionException
     * @covers ::handleRequest
     */
    public function testHandleRequest(): void
    {
        $type = 'abc';
        $name = 'def';
        $numberOfResults = 42;
        $indexOfFirstResult = 1337;
        $countNames = 21;

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        /* @var RecipeDataCollection&MockObject $limitedRecipeData */
        $limitedRecipeData = $this->createMock(RecipeDataCollection::class);
        /* @var GenericEntityWithRecipes&MockObject $responseItem */
        $responseItem = $this->createMock(GenericEntityWithRecipes::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var ItemIngredientRequest&MockObject $request */
        $request = $this->createMock(ItemIngredientRequest::class);
        $request->expects($this->once())
                ->method('getType')
                ->willReturn($type);
        $request->expects($this->once())
                ->method('getName')
                ->willReturn($name);
        $request->expects($this->once())
                ->method('getNumberOfResults')
                ->willReturn($numberOfResults);
        $request->expects($this->once())
                ->method('getIndexOfFirstResult')
                ->willReturn($indexOfFirstResult);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('limitNames')
                   ->with($this->identicalTo($numberOfResults), $this->identicalTo($indexOfFirstResult))
                   ->willReturn($limitedRecipeData);
        $recipeData->expects($this->once())
                   ->method('countNames')
                   ->willReturn($countNames);

        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->onlyMethods([
                            'getAuthorizationToken',
                            'fetchItem',
                            'fetchRecipeData',
                            'createResponseEntity',
                            'createResponse',
                        ])
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('fetchItem')
                ->with($this->identicalTo($type), $this->identicalTo($name), $this->identicalTo($authorizationToken))
                ->willReturn($item);
        $handler->expects($this->once())
                ->method('fetchRecipeData')
                ->with($this->identicalTo($item))
                ->willReturn($recipeData);
        $handler->expects($this->once())
                ->method('createResponseEntity')
                ->with(
                    $this->identicalTo($item),
                    $this->identicalTo($limitedRecipeData),
                    $this->identicalTo($countNames)
                )
                ->willReturn($responseItem);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->identicalTo($responseItem))
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the fetchItem method.
     * @throws ReflectionException
     * @covers ::fetchItem
     */
    public function testFetchItem(): void
    {
        $type = 'abc';
        $name = 'def';

        $expectedNamesByTypes = new NamesByTypes();
        $expectedNamesByTypes->addName('abc', 'def');

        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        $items = [$item];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->itemRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with(
                                 $this->identicalTo($combinationId),
                                 $this->equalTo($expectedNamesByTypes)
                             )
                             ->willReturn($items);

        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($handler, 'fetchItem', $type, $name, $authorizationToken);

        $this->assertSame($item, $result);
    }

    /**
     * Tests the fetchItem method.
     * @throws ReflectionException
     * @covers ::fetchItem
     */
    public function testFetchItemWithException(): void
    {
        $type = 'abc';
        $name = 'def';

        $expectedNamesByTypes = new NamesByTypes();
        $expectedNamesByTypes->addName('abc', 'def');

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->itemRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with(
                                 $this->identicalTo($combinationId),
                                 $this->equalTo($expectedNamesByTypes)
                             )
                             ->willReturn([]);

        $this->expectException(EntityNotFoundException::class);

        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();

        $this->invokeMethod($handler, 'fetchItem', $type, $name, $authorizationToken);
    }

    /**
     * Tests the createResponseEntity method.
     * @throws ReflectionException
     * @covers ::createResponseEntity
     */
    public function testCreateResponseEntity(): void
    {
        $totalNumberOfRecipes = 42;

        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);

        $expectedResult = new GenericEntityWithRecipes();
        $expectedResult->setTotalNumberOfRecipes($totalNumberOfRecipes);

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($item), $this->isInstanceOf(GenericEntityWithRecipes::class)],
                                [$this->identicalTo($recipeData), $this->isInstanceOf(GenericEntityWithRecipes::class)]
                            );

        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($handler, 'createResponseEntity', $item, $recipeData, $totalNumberOfRecipes);

        $this->assertEquals($expectedResult, $result);
    }
}
