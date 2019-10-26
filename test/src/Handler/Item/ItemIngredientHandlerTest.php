<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\TestHelper\ReflectionTrait;
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
     * Tests the fetchRecipeData method.
     * @throws ReflectionException
     * @covers ::fetchRecipeData
     */
    public function testFetchRecipeData(): void
    {
        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithIngredients')
                            ->with($this->identicalTo([$item]), $this->identicalTo($authorizationToken))
                            ->willReturn($recipeDataCollection);

        /* @var ItemIngredientHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ItemIngredientHandler::class)
                        ->onlyMethods(['getAuthorizationToken'])
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);

        $result = $this->invokeMethod($handler, 'fetchRecipeData', $item);

        $this->assertSame($recipeDataCollection, $result);
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
