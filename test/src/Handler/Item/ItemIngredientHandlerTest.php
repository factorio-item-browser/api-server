<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Item\ItemIngredientRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemIngredientResponse;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemIngredientHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ItemIngredientHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\ItemIngredientHandler
 */
class ItemIngredientHandlerTest extends TestCase
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
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = ItemIngredientRequest::class;

        $handler = new ItemIngredientHandler($this->itemRepository, $this->mapperManager, $this->recipeService);
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
        /* @var ItemIngredientResponse&MockObject $response */
        $response = $this->createMock(ItemIngredientResponse::class);

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

        $this->recipeService->expects($this->once())
                            ->method('getDataWithIngredients')
                            ->with($this->equalTo([$item]), $this->identicalTo($authorizationToken))
                            ->willReturn($recipeData);

        /* @var ItemIngredientHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ItemIngredientHandler::class)
                        ->setMethods(['getAuthorizationToken', 'fetchItem', 'createResponseEntity', 'createResponse'])
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('fetchItem')
                ->with($this->identicalTo($type), $this->identicalTo($name), $this->identicalTo($authorizationToken))
                ->willReturn($item);
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
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        /* @var GenericEntityWithRecipes&MockObject $item */
        $item = $this->createMock(GenericEntityWithRecipes::class);

        $expectedResult = new ItemIngredientResponse();
        $expectedResult->setItem($item);

        $handler = new ItemIngredientHandler($this->itemRepository, $this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'createResponse', $item);

        $this->assertEquals($expectedResult, $result);
    }
}
