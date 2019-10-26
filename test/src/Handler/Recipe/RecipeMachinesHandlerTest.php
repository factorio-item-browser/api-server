<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Recipe;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Machine as ClientMachine;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeMachinesRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeMachinesHandler;
use FactorioItemBrowser\Api\Server\Service\MachineService;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeMachinesHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeMachinesHandler
 */
class RecipeMachinesHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked machine service.
     * @var MachineService&MockObject
     */
    protected $machineService;

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

        $this->machineService = $this->createMock(MachineService::class);
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
        $handler = new RecipeMachinesHandler($this->machineService, $this->mapperManager, $this->recipeService);

        $this->assertSame($this->machineService, $this->extractProperty($handler, 'machineService'));
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
        $expectedResult = RecipeMachinesRequest::class;

        $handler = new RecipeMachinesHandler($this->machineService, $this->mapperManager, $this->recipeService);
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
        $indexOfFirstResult = 1;
        $numberOfResults = 2;

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);
        /* @var RecipeMachinesResponse&MockObject $response */
        $response = $this->createMock(RecipeMachinesResponse::class);

        /* @var DatabaseMachine&MockObject $machine1 */
        $machine1 = $this->createMock(DatabaseMachine::class);
        /* @var DatabaseMachine&MockObject $machine2 */
        $machine2 = $this->createMock(DatabaseMachine::class);
        /* @var DatabaseMachine&MockObject $machine3 */
        $machine3 = $this->createMock(DatabaseMachine::class);
        /* @var DatabaseMachine&MockObject $machine4 */
        $machine4 = $this->createMock(DatabaseMachine::class);

        $machines = [$machine1, $machine2, $machine3, $machine4];
        $limitedMachines = [$machine2, $machine3];

        /* @var RecipeMachinesRequest&MockObject $request */
        $request = $this->createMock(RecipeMachinesRequest::class);
        $request->expects($this->once())
                ->method('getIndexOfFirstResult')
                ->willReturn($indexOfFirstResult);
        $request->expects($this->once())
                ->method('getNumberOfResults')
                ->willReturn($numberOfResults);

        /* @var RecipeMachinesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(RecipeMachinesHandler::class)
                        ->onlyMethods([
                            'getAuthorizationToken',
                            'fetchRecipe',
                            'fetchMachines',
                            'createResponse',
                        ])
                        ->disableOriginalConstructor()
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('fetchRecipe')
                ->with($this->identicalTo($request), $this->identicalTo($authorizationToken))
                ->willReturn($recipe);
        $handler->expects($this->once())
                ->method('fetchMachines')
                ->with($this->identicalTo($recipe), $this->identicalTo($authorizationToken))
                ->willReturn($machines);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->equalTo($limitedMachines), $this->identicalTo(4))
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the fetchRecipe method.
     * @throws ReflectionException
     * @covers ::fetchRecipe
     */
    public function testFetchRecipe(): void
    {
        $name = 'abc';

        /* @var UuidInterface&MockObject $recipeId */
        $recipeId = $this->createMock(UuidInterface::class);
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        /* @var RecipeMachinesRequest&MockObject $request */
        $request = $this->createMock(RecipeMachinesRequest::class);
        $request->expects($this->once())
                ->method('getName')
                ->willReturn($name);

        /* @var RecipeData&MockObject $firstData */
        $firstData = $this->createMock(RecipeData::class);
        $firstData->expects($this->once())
                  ->method('getId')
                  ->willReturn($recipeId);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getFirstValue')
                   ->willReturn($firstData);

        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithNames')
                            ->with($this->identicalTo([$name]), $this->identicalTo($authorizationToken))
                            ->willReturn($recipeData);
        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo([$recipeId]))
                            ->willReturn([$recipe]);

        $handler = new RecipeMachinesHandler($this->machineService, $this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'fetchRecipe', $request, $authorizationToken);

        $this->assertSame($recipe, $result);
    }

    /**
     * Tests the fetchRecipe method without receiving recipe data.
     * @throws ReflectionException
     * @covers ::fetchRecipe
     */
    public function testFetchRecipeWithoutData(): void
    {
        $name = 'abc';

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        /* @var RecipeMachinesRequest&MockObject $request */
        $request = $this->createMock(RecipeMachinesRequest::class);
        $request->expects($this->atLeastOnce())
                ->method('getName')
                ->willReturn($name);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getFirstValue')
                   ->willReturn(null);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithNames')
                            ->with($this->identicalTo([$name]), $this->identicalTo($authorizationToken))
                            ->willReturn($recipeData);
        $this->recipeService->expects($this->never())
                            ->method('getDetailsByIds');

        $this->expectException(EntityNotFoundException::class);

        $handler = new RecipeMachinesHandler($this->machineService, $this->mapperManager, $this->recipeService);
        $this->invokeMethod($handler, 'fetchRecipe', $request, $authorizationToken);
    }

    /**
     * Tests the fetchRecipe method without receiving a recipe entity (should never happen).
     * @throws ReflectionException
     * @covers ::fetchRecipe
     */
    public function testFetchRecipeWithoutEntity(): void
    {
        $name = 'abc';

        /* @var UuidInterface&MockObject $recipeId */
        $recipeId = $this->createMock(UuidInterface::class);
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        /* @var RecipeMachinesRequest&MockObject $request */
        $request = $this->createMock(RecipeMachinesRequest::class);
        $request->expects($this->atLeastOnce())
                ->method('getName')
                ->willReturn($name);

        /* @var RecipeData&MockObject $firstData */
        $firstData = $this->createMock(RecipeData::class);
        $firstData->expects($this->once())
                  ->method('getId')
                  ->willReturn($recipeId);

        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getFirstValue')
                   ->willReturn($firstData);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithNames')
                            ->with($this->identicalTo([$name]), $this->identicalTo($authorizationToken))
                            ->willReturn($recipeData);
        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo([$recipeId]))
                            ->willReturn([]);

        $this->expectException(EntityNotFoundException::class);

        $handler = new RecipeMachinesHandler($this->machineService, $this->mapperManager, $this->recipeService);
        $this->invokeMethod($handler, 'fetchRecipe', $request, $authorizationToken);
    }

    /**
     * Tests the fetchMachines method.
     * @throws ReflectionException
     * @covers ::fetchMachines
     */
    public function testFetchMachines(): void
    {
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        /* @var CraftingCategory&MockObject $craftingCategory */
        $craftingCategory = $this->createMock(CraftingCategory::class);

        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);
        $recipe->expects($this->once())
                ->method('getCraftingCategory')
                ->willReturn($craftingCategory);

        $databaseMachines = [
            $this->createMock(DatabaseMachine::class),
            $this->createMock(DatabaseMachine::class),
        ];
        $filteredMachines = [
            $this->createMock(DatabaseMachine::class),
            $this->createMock(DatabaseMachine::class),
        ];
        $sortedMachines = [
            $this->createMock(DatabaseMachine::class),
            $this->createMock(DatabaseMachine::class),
        ];

        $this->machineService->expects($this->once())
                             ->method('getMachinesByCraftingCategory')
                             ->with($this->identicalTo($craftingCategory), $this->identicalTo($authorizationToken))
                             ->willReturn($databaseMachines);
        $this->machineService->expects($this->once())
                             ->method('filterMachinesForRecipe')
                             ->with($this->identicalTo($databaseMachines), $this->identicalTo($recipe))
                             ->willReturn($filteredMachines);
        $this->machineService->expects($this->once())
                             ->method('sortMachines')
                             ->with($this->identicalTo($filteredMachines))
                             ->willReturn($sortedMachines);

        $handler = new RecipeMachinesHandler($this->machineService, $this->mapperManager, $this->recipeService);
        $result = $this->invokeMethod($handler, 'fetchMachines', $recipe, $authorizationToken);

        $this->assertSame($sortedMachines, $result);
    }

    /**
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        $totalNumberOfMachines = 42;

        /* @var DatabaseMachine&MockObject $databaseMachine1 */
        $databaseMachine1 = $this->createMock(DatabaseMachine::class);
        /* @var DatabaseMachine&MockObject $databaseMachine2 */
        $databaseMachine2 = $this->createMock(DatabaseMachine::class);
        /* @var ClientMachine&MockObject $clientMachine1 */
        $clientMachine1 = $this->createMock(ClientMachine::class);
        /* @var ClientMachine&MockObject $clientMachine2 */
        $clientMachine2 = $this->createMock(ClientMachine::class);

        $databaseMachines = [$databaseMachine1, $databaseMachine2];
        $expectedResult = new RecipeMachinesResponse();
        $expectedResult->setMachines([$clientMachine1, $clientMachine2])
                       ->setTotalNumberOfResults($totalNumberOfMachines);

        /* @var RecipeMachinesHandler&MockObject $handler */
        $handler = $this->getMockBuilder(RecipeMachinesHandler::class)
                        ->onlyMethods(['mapMachine'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('mapMachine')
                ->withConsecutive(
                    [$this->identicalTo($databaseMachine1)],
                    [$this->identicalTo($databaseMachine2)]
                )
                ->willReturnOnConsecutiveCalls(
                    $clientMachine1,
                    $clientMachine2
                );

        $result = $this->invokeMethod($handler, 'createResponse', $databaseMachines, $totalNumberOfMachines);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the mapMachine method.
     * @throws ReflectionException
     * @covers ::mapMachine
     */
    public function testMapMachine(): void
    {
        /* @var DatabaseMachine&MockObject $databaseMachine */
        $databaseMachine = $this->createMock(DatabaseMachine::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($databaseMachine), $this->isInstanceOf(ClientMachine::class));

        $handler = new RecipeMachinesHandler($this->machineService, $this->mapperManager, $this->recipeService);
        $this->invokeMethod($handler, 'mapMachine', $databaseMachine);
    }
}
