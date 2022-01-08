<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Recipe;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\Machine as ClientMachine;
use FactorioItemBrowser\Api\Client\Request\Recipe\RecipeMachinesRequest;
use FactorioItemBrowser\Api\Client\Response\Recipe\RecipeMachinesResponse;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeMachinesHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\MachineService;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeMachinesHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeMachinesHandler
 */
class RecipeMachinesHandlerTest extends TestCase
{
    use ReflectionTrait;

    /** @var MachineService&MockObject */
    private MachineService $machineService;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeService&MockObject */
    private RecipeService $recipeService;

    protected function setUp(): void
    {
        $this->machineService = $this->createMock(MachineService::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeService = $this->createMock(RecipeService::class);
    }

    /**
     * @throws ServerException
     */
    public function testHandle(): void
    {
        $clientRequest = new RecipeMachinesRequest();
        $clientRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $clientRequest->name = 'abc';
        $clientRequest->numberOfResults = 2;
        $clientRequest->indexOfFirstResult = 1;

        $machine1 = $this->createMock(DatabaseMachine::class);
        $machine2 = $this->createMock(DatabaseMachine::class);
        $mappedMachine1 = $this->createMock(ClientMachine::class);
        $mappedMachine2 = $this->createMock(ClientMachine::class);

        $machines = [
            $this->createMock(DatabaseMachine::class),
            $machine1,
            $machine2,
            $this->createMock(DatabaseMachine::class),
        ];

        $recipe = $this->createMock(Recipe::class);
        $expectedPayload = new RecipeMachinesResponse();
        $expectedPayload->machines = [$mappedMachine1, $mappedMachine2];
        $expectedPayload->totalNumberOfResults = 4;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($machine1), $this->isInstanceOf(ClientMachine::class)],
                                [$this->identicalTo($machine2), $this->isInstanceOf(ClientMachine::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $mappedMachine1,
                                $mappedMachine2
                            );

        $instance = $this->createInstance(['fetchRecipe', 'fetchMachines']);
        $instance->expects($this->once())
                 ->method('fetchRecipe')
                 ->with(
                     $this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')),
                     $this->identicalTo('abc'),
                 )
                 ->willReturn($recipe);
        $instance->expects($this->once())
                 ->method('fetchMachines')
                 ->with(
                     $this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')),
                     $this->identicalTo($recipe)
                 )
                 ->willReturn($machines);

        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeMachinesHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeMachinesHandler
    {
        return $this->getMockBuilder(RecipeMachinesHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->machineService,
                        $this->mapperManager,
                        $this->recipeService,
                    ])
                    ->getMock();
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchRecipe(): void
    {
        $name = 'abc';
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
        $recipeId = Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1');

        $recipeData = new RecipeData();
        $recipeData->setId($recipeId);
        $recipeDataCollection = new RecipeDataCollection();
        $recipeDataCollection->add($recipeData);
        $recipe = $this->createMock(Recipe::class);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithNames')
                            ->with(
                                $this->identicalTo($combinationId),
                                $this->identicalTo([$name])
                            )
                            ->willReturn($recipeDataCollection);
        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo([$recipeId]))
                            ->willReturn([$recipe]);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchRecipe', $combinationId, $name);

        $this->assertSame($recipe, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchRecipeWithoutRecipeData(): void
    {
        $name = 'abc';
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');

        $recipeDataCollection = new RecipeDataCollection();

        $this->recipeService->expects($this->once())
                            ->method('getDataWithNames')
                            ->with(
                                $this->identicalTo($combinationId),
                                $this->identicalTo([$name])
                            )
                            ->willReturn($recipeDataCollection);
        $this->recipeService->expects($this->never())
                            ->method('getDetailsByIds');

        $this->expectException(EntityNotFoundException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'fetchRecipe', $combinationId, $name);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchRecipeWithoutEntity(): void
    {
        $name = 'abc';
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
        $recipeId = Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1');

        $recipeData = new RecipeData();
        $recipeData->setId($recipeId);
        $recipeDataCollection = new RecipeDataCollection();
        $recipeDataCollection->add($recipeData);

        $this->recipeService->expects($this->once())
                            ->method('getDataWithNames')
                            ->with(
                                $this->identicalTo($combinationId),
                                $this->identicalTo([$name])
                            )
                            ->willReturn($recipeDataCollection);
        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo([$recipeId]))
                            ->willReturn([]);

        $this->expectException(EntityNotFoundException::class);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'fetchRecipe', $combinationId, $name);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchMachines(): void
    {
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
        $craftingCategory = $this->createMock(CraftingCategory::class);

        $recipe = new Recipe();
        $recipe->setCraftingCategory($craftingCategory);

        $machines = [
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
                             ->with($this->identicalTo($combinationId), $this->identicalTo($craftingCategory))
                             ->willReturn($machines);
        $this->machineService->expects($this->once())
                             ->method('filterMachinesForRecipe')
                             ->with($this->identicalTo($machines), $this->identicalTo($recipe))
                             ->willReturn($filteredMachines);
        $this->machineService->expects($this->once())
                             ->method('sortMachines')
                             ->with($this->identicalTo($filteredMachines))
                             ->willReturn($sortedMachines);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchMachines', $combinationId, $recipe);

        $this->assertSame($sortedMachines, $result);
    }
}
