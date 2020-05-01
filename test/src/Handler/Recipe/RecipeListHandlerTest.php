<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Recipe;

use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeListRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeListResponse;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeListHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeListHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeListHandler
 */
class RecipeListHandlerTest extends TestCase
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
        $handler = new RecipeListHandler($this->mapperManager, $this->recipeService);

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
        $expectedResult = RecipeListRequest::class;

        $handler = new RecipeListHandler($this->mapperManager, $this->recipeService);
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
        $numberOfResults = 42;
        $indexOfFirstResult = 21;
        $numberOfRecipes = 1337;

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        /* @var RecipeDataCollection&MockObject $limitedRecipeData */
        $limitedRecipeData = $this->createMock(RecipeDataCollection::class);
        /* @var RecipeListResponse&MockObject $response */
        $response = $this->createMock(RecipeListResponse::class);

        $mappedRecipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('limitNames')
                   ->with($this->identicalTo($numberOfResults), $this->identicalTo($indexOfFirstResult))
                   ->willReturn($limitedRecipeData);
        $recipeData->expects($this->once())
                   ->method('countNames')
                   ->willReturn($numberOfRecipes);

        /* @var RecipeListRequest&MockObject $request */
        $request = $this->createMock(RecipeListRequest::class);
        $request->expects($this->once())
                ->method('getNumberOfResults')
                ->willReturn($numberOfResults);
        $request->expects($this->once())
                ->method('getIndexOfFirstResult')
                ->willReturn($indexOfFirstResult);

        $this->recipeService->expects($this->once())
                            ->method('getAllData')
                            ->with($this->identicalTo($authorizationToken))
                            ->willReturn($recipeData);

        /* @var RecipeListHandler&MockObject $handler */
        $handler = $this->getMockBuilder(RecipeListHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'mapRecipes', 'createResponse'])
                        ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('mapRecipes')
                ->with($this->identicalTo($limitedRecipeData))
                ->willReturn($mappedRecipes);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->identicalTo($mappedRecipes), $this->identicalTo($numberOfRecipes))
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the mapRecipes method.
     * @throws ReflectionException
     * @covers ::mapRecipes
     */
    public function testMapRecipes(): void
    {
        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($recipeData),
                                $this->isInstanceOf(GenericEntityWithRecipes::class)
                            );

        $handler = new RecipeListHandler($this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'mapRecipes', $recipeData);

        $this->assertSame([], $result);
    }

    /**
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        $recipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];
        $numberOfRecipes = 42;

        $expectedResult = new RecipeListResponse();
        $expectedResult->setRecipes($recipes)
                       ->setTotalNumberOfResults($numberOfRecipes);

        $handler = new RecipeListHandler($this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'createResponse', $recipes, $numberOfRecipes);

        $this->assertEquals($expectedResult, $result);
    }
}
