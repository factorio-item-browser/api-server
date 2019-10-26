<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Recipe;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeDetailsResponse;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeDetailsHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeDetailsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeDetailsHandler
 */
class RecipeDetailsHandlerTest extends TestCase
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
     * @throws ReflectionException
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
        $handler = new RecipeDetailsHandler($this->mapperManager, $this->recipeService);

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
        $expectedResult = RecipeDetailsRequest::class;

        $handler = new RecipeDetailsHandler($this->mapperManager, $this->recipeService);
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
        $names = ['abc', 'def'];

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        /* @var RecipeDetailsResponse&MockObject $response */
        $response = $this->createMock(RecipeDetailsResponse::class);

        $mappedRecipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];

        /* @var RecipeDetailsRequest&MockObject $request */
        $request = $this->createMock(RecipeDetailsRequest::class);
        $request->expects($this->once())
                ->method('getNames')
                ->willReturn($names);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithNames')
                            ->with($this->identicalTo($names), $this->identicalTo($authorizationToken))
                            ->willReturn($recipeData);

        /* @var RecipeDetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(RecipeDetailsHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'mapRecipes', 'createResponse'])
                        ->setConstructorArgs([$this->mapperManager, $this->recipeService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('mapRecipes')
                ->with($this->identicalTo($recipeData))
                ->willReturn($mappedRecipes);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->identicalTo($mappedRecipes))
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

        $handler = new RecipeDetailsHandler($this->mapperManager, $this->recipeService);
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
        $expectedResult = new RecipeDetailsResponse();
        $expectedResult->setRecipes($recipes);

        $handler = new RecipeDetailsHandler($this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'createResponse', $recipes);

        $this->assertEquals($expectedResult, $result);
    }
}
