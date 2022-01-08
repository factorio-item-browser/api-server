<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\Api\Client\Transfer\Entity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericDetailsHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the GenericDetailsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Generic\GenericDetailsHandler
 */
class GenericDetailsHandlerTest extends TestCase
{
    use ReflectionTrait;

    /** @var ItemRepository&MockObject */
    private ItemRepository $itemRepository;
    /** @var MachineRepository&MockObject */
    private MachineRepository $machineRepository;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeRepository&MockObject */
    private RecipeRepository $recipeRepository;

    protected function setUp(): void
    {
        $this->itemRepository = $this->createMock(ItemRepository::class);
        $this->machineRepository = $this->createMock(MachineRepository::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeRepository = $this->createMock(RecipeRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return GenericDetailsHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): GenericDetailsHandler
    {
        return $this->getMockBuilder(GenericDetailsHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->itemRepository,
                        $this->machineRepository,
                        $this->mapperManager,
                        $this->recipeRepository,
                    ])
                    ->getMock();
    }

    public function testHandle(): void
    {
        $requestEntities = [
            $this->createMock(Entity::class),
            $this->createMock(Entity::class),
        ];
        $clientRequest = new GenericDetailsRequest();
        $clientRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $clientRequest->entities = $requestEntities;

        $namesByTypes = $this->createMock(NamesByTypes::class);

        $item1 = $this->createMock(GenericEntity::class);
        $item2 = $this->createMock(GenericEntity::class);
        $machine1 = $this->createMock(GenericEntity::class);
        $machine2 = $this->createMock(GenericEntity::class);
        $recipe1 = $this->createMock(GenericEntity::class);
        $recipe2 = $this->createMock(GenericEntity::class);

        $items = ['abc' => $item1, 'def' => $item2];
        $machines = ['ghi' => $machine1, 'jkl' => $machine2];
        $recipes = ['mno' => $recipe1, 'pqr' => $recipe2];
        $expectedPayload = new GenericDetailsResponse();
        $expectedPayload->entities = [$item1, $item2, $machine1, $machine2, $recipe1, $recipe2];

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);

        $instance = $this->createInstance([
            'extractTypesAndNames',
            'processItems',
            'processMachines',
            'processRecipes',
        ]);
        $instance->expects($this->once())
                 ->method('extractTypesAndNames')
                 ->with($requestEntities)
                 ->willReturn($namesByTypes);
        $instance->expects($this->once())
                 ->method('processItems')
                 ->with(
                     $this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')),
                     $this->identicalTo($namesByTypes),
                 )
                 ->willReturn($items);
        $instance->expects($this->once())
                 ->method('processMachines')
                 ->with(
                     $this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')),
                     $this->identicalTo($namesByTypes),
                 )
                 ->willReturn($machines);
        $instance->expects($this->once())
                 ->method('processRecipes')
                 ->with(
                     $this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')),
                     $this->identicalTo($namesByTypes),
                 )
                 ->willReturn($recipes);
        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    /**
     * @throws ReflectionException
     */
    public function testProcessItems(): void
    {
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
        $namesByTypes = $this->createMock(NamesByTypes::class);

        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];
        $mappedItems = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];

        $this->itemRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with($this->identicalTo($combinationId), $this->identicalTo($namesByTypes))
                             ->willReturn($items);

        $instance = $this->createInstance(['mapObjectsToEntities']);
        $instance->expects($this->once())
                 ->method('mapObjectsToEntities')
                 ->with($this->identicalTo($items))
                 ->willReturn($mappedItems);

        $result = $this->invokeMethod($instance, 'processItems', $combinationId, $namesByTypes);

        $this->assertSame($mappedItems, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testProcessMachines(): void
    {
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
        $machineNames = ['abc', 'def'];

        $namesByTypes = $this->createMock(NamesByTypes::class);
        $namesByTypes->expects($this->once())
                     ->method('getNames')
                     ->with($this->identicalTo(EntityType::MACHINE))
                     ->willReturn($machineNames);

        $machines = [
            $this->createMock(Machine::class),
            $this->createMock(Machine::class),
        ];
        $mappedMachines = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];

        $this->machineRepository->expects($this->once())
                                ->method('findByNames')
                                ->with(
                                    $this->identicalTo($combinationId),
                                    $this->identicalTo($machineNames),
                                )
                                ->willReturn($machines);

        $instance = $this->createInstance(['mapObjectsToEntities']);
        $instance->expects($this->once())
                 ->method('mapObjectsToEntities')
                 ->with($this->identicalTo($machines))
                 ->willReturn($mappedMachines);

        $result = $this->invokeMethod($instance, 'processMachines', $combinationId, $namesByTypes);

        $this->assertSame($mappedMachines, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testProcessRecipes(): void
    {
        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
        $recipeNames = ['abc', 'def'];

        $namesByTypes = $this->createMock(NamesByTypes::class);
        $namesByTypes->expects($this->once())
                     ->method('getNames')
                     ->with($this->identicalTo(EntityType::RECIPE))
                     ->willReturn($recipeNames);

        $recipes = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];
        $mappedRecipes = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];

        $this->recipeRepository->expects($this->once())
                                ->method('findDataByNames')
                                ->with(
                                    $this->identicalTo($combinationId),
                                    $this->identicalTo($recipeNames),
                                )
                                ->willReturn($recipes);

        $instance = $this->createInstance(['mapObjectsToEntities']);
        $instance->expects($this->once())
                 ->method('mapObjectsToEntities')
                 ->with($this->identicalTo($recipes))
                 ->willReturn($mappedRecipes);

        $result = $this->invokeMethod($instance, 'processRecipes', $combinationId, $namesByTypes);

        $this->assertSame($mappedRecipes, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapObjectsToEntities(): void
    {
        $object1 = $this->createMock(Item::class);
        $object2 = $this->createMock(Machine::class);
        $object3 = $this->createMock(RecipeData::class);
        $objects = [$object1, $object2, $object3];

        $entity1 = new GenericEntity();
        $entity1->type = 'abc';
        $entity1->name = 'def';
        $entity2 = new GenericEntity();
        $entity2->type = 'abc';
        $entity2->name = 'ghi';
        $entity3 = new GenericEntity();
        $entity3->type = 'jkl';
        $entity3->name = 'mno';

        $expectedResult = [
            'abc|def' => $entity1,
            'abc|ghi' => $entity2,
            'jkl|mno' => $entity3,
        ];

        $this->mapperManager->expects($this->exactly(3))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($object1), $this->isInstanceOf(GenericEntity::class)],
                                [$this->identicalTo($object2), $this->isInstanceOf(GenericEntity::class)],
                                [$this->identicalTo($object3), $this->isInstanceOf(GenericEntity::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $entity1,
                                $entity2,
                                $entity3,
                            );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'mapObjectsToEntities', $objects);

        $this->assertSame($expectedResult, $result);
    }
}
