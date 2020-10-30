<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericDetailsHandler;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;
use stdClass;

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

    /**
     * The mocked item repository.
     * @var ItemRepository&MockObject
     */
    protected $itemRepository;

    /**
     * The mocked machine repository.
     * @var MachineRepository&MockObject
     */
    protected $machineRepository;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * The mocked recipe repository.
     * @var RecipeRepository&MockObject
     */
    protected $recipeRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->itemRepository = $this->createMock(ItemRepository::class);
        $this->machineRepository = $this->createMock(MachineRepository::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeRepository = $this->createMock(RecipeRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new GenericDetailsHandler(
            $this->itemRepository,
            $this->machineRepository,
            $this->mapperManager,
            $this->recipeRepository
        );

        $this->assertSame($this->itemRepository, $this->extractProperty($handler, 'itemRepository'));
        $this->assertSame($this->machineRepository, $this->extractProperty($handler, 'machineRepository'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
        $this->assertSame($this->recipeRepository, $this->extractProperty($handler, 'recipeRepository'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = GenericDetailsRequest::class;

        $handler = new GenericDetailsHandler(
            $this->itemRepository,
            $this->machineRepository,
            $this->mapperManager,
            $this->recipeRepository
        );
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
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        /* @var GenericDetailsResponse&MockObject $response */
        $response = $this->createMock(GenericDetailsResponse::class);
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        $entities = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];
        $requestEntities = [
            $this->createMock(Entity::class),
            $this->createMock(Entity::class),
        ];

        /* @var GenericDetailsRequest&MockObject $request */
        $request = $this->createMock(GenericDetailsRequest::class);
        $request->expects($this->once())
                ->method('getEntities')
                ->willReturn($requestEntities);

        /* @var GenericDetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->onlyMethods([
                            'extractTypesAndNames',
                            'getAuthorizationToken',
                            'process',
                            'createResponse'
                        ])
                        ->disableOriginalConstructor()
                        ->getMock();
        $handler->expects($this->once())
                ->method('extractTypesAndNames')
                ->with($this->identicalTo($requestEntities))
                ->willReturn($namesByTypes);
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('process')
                ->with($this->identicalTo($namesByTypes), $this->identicalTo($authorizationToken))
                ->willReturn($entities);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->equalTo($entities))
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the process method.
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcess(): void
    {
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        /* @var GenericEntity&MockObject $entity1 */
        $entity1 = $this->createMock(GenericEntity::class);
        /* @var GenericEntity&MockObject $entity2 */
        $entity2 = $this->createMock(GenericEntity::class);
        /* @var GenericEntity&MockObject $entity3 */
        $entity3 = $this->createMock(GenericEntity::class);
        /* @var GenericEntity&MockObject $entity4 */
        $entity4 = $this->createMock(GenericEntity::class);
        /* @var GenericEntity&MockObject $entity5 */
        $entity5 = $this->createMock(GenericEntity::class);
        /* @var GenericEntity&MockObject $entity6 */
        $entity6 = $this->createMock(GenericEntity::class);

        $expectedResult = [
            $entity1,
            $entity2,
            $entity3,
            $entity4,
            $entity5,
            $entity6,
        ];

        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);

        /* @var GenericDetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->onlyMethods([
                            'processItems',
                            'processMachines',
                            'processRecipes',
                        ])
                        ->disableOriginalConstructor()
                        ->getMock();
        $handler->expects($this->once())
                ->method('processItems')
                ->with($this->identicalTo($namesByTypes), $this->identicalTo($authorizationToken))
                ->willReturn(['abc' => $entity1, 'def' => $entity2]);
        $handler->expects($this->once())
                ->method('processMachines')
                ->with($this->identicalTo($namesByTypes), $this->identicalTo($authorizationToken))
                ->willReturn(['ghi' => $entity3, 'jkl' => $entity4]);
        $handler->expects($this->once())
                ->method('processRecipes')
                ->with($this->identicalTo($namesByTypes), $this->identicalTo($authorizationToken))
                ->willReturn(['mno' => $entity5, 'pqr' => $entity6]);

        $result = $this->invokeMethod($handler, 'process', $namesByTypes, $authorizationToken);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the processItems method.
     * @throws ReflectionException
     * @covers ::processItems
     */
    public function testProcessItems(): void
    {
        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);

        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];
        $mappedItems = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->itemRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with(
                                 $this->identicalTo($combinationId),
                                 $this->identicalTo($namesByTypes)
                             )
                             ->willReturn($items);

        /* @var GenericDetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->onlyMethods(['mapObjectsToEntities'])
                        ->setConstructorArgs([
                            $this->itemRepository,
                            $this->machineRepository,
                            $this->mapperManager,
                            $this->recipeRepository
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('mapObjectsToEntities')
                ->with($this->identicalTo($items))
                ->willReturn($mappedItems);

        $result = $this->invokeMethod($handler, 'processItems', $namesByTypes, $authorizationToken);

        $this->assertSame($mappedItems, $result);
    }

    /**
     * Tests the processMachines method.
     * @throws ReflectionException
     * @covers ::processMachines
     */
    public function testProcessMachines(): void
    {
        $names = ['abc', 'def'];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);

        $machines = [
            $this->createMock(Machine::class),
            $this->createMock(Machine::class),
        ];
        $mappedMachines = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];

        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        $namesByTypes->expects($this->once())
                     ->method('getNames')
                     ->with($this->identicalTo(EntityType::MACHINE))
                     ->willReturn($names);


        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->machineRepository->expects($this->once())
                             ->method('findByNames')
                             ->with(
                                 $this->equalTo($combinationId),
                                 $this->identicalTo($names)
                             )
                             ->willReturn($machines);

        /* @var GenericDetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->onlyMethods(['mapObjectsToEntities'])
                        ->setConstructorArgs([
                            $this->itemRepository,
                            $this->machineRepository,
                            $this->mapperManager,
                            $this->recipeRepository
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('mapObjectsToEntities')
                ->with($this->identicalTo($machines))
                ->willReturn($mappedMachines);

        $result = $this->invokeMethod($handler, 'processMachines', $namesByTypes, $authorizationToken);

        $this->assertSame($mappedMachines, $result);
    }

    /**
     * Tests the processRecipes method.
     * @throws ReflectionException
     * @covers ::processRecipes
     */
    public function testProcessRecipes(): void
    {
        $names = ['abc', 'def'];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);

        $recipes = [
            $this->createMock(Recipe::class),
            $this->createMock(Recipe::class),
        ];
        $mappedRecipes = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];

        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        $namesByTypes->expects($this->once())
                     ->method('getNames')
                     ->with($this->identicalTo(EntityType::RECIPE))
                     ->willReturn($names);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->recipeRepository->expects($this->once())
                             ->method('findDataByNames')
                             ->with(
                                 $this->identicalTo($combinationId),
                                 $this->identicalTo($names)
                             )
                             ->willReturn($recipes);

        /* @var GenericDetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->onlyMethods(['mapObjectsToEntities'])
                        ->setConstructorArgs([
                            $this->itemRepository,
                            $this->machineRepository,
                            $this->mapperManager,
                            $this->recipeRepository
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('mapObjectsToEntities')
                ->with($this->identicalTo($recipes))
                ->willReturn($mappedRecipes);

        $result = $this->invokeMethod($handler, 'processRecipes', $namesByTypes, $authorizationToken);

        $this->assertSame($mappedRecipes, $result);
    }

    /**
     * Tests the mapObjectsToEntities method.
     * @throws ReflectionException
     * @covers ::mapObjectsToEntities
     */
    public function testMapObjectsToEntities(): void
    {
        /* @var stdClass&MockObject $object1 */
        $object1 = $this->createMock(stdClass::class);
        /* @var stdClass&MockObject $object2 */
        $object2 = $this->createMock(stdClass::class);

        $objects = [$object1, $object2];
        $expectedResult = [
            'abc' => new GenericEntity(),
            'def' => new GenericEntity(),
        ];

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($object1), $this->isInstanceOf(GenericEntity::class)],
                                [$this->identicalTo($object2), $this->isInstanceOf(GenericEntity::class)]
                            );

        /* @var GenericDetailsHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->onlyMethods(['getEntityKey'])
                        ->setConstructorArgs([
                            $this->itemRepository,
                            $this->machineRepository,
                            $this->mapperManager,
                            $this->recipeRepository
                        ])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('getEntityKey')
                ->withConsecutive(
                    [$this->isInstanceOf(GenericEntity::class)],
                    [$this->isInstanceOf(GenericEntity::class)]
                )
                ->willReturnOnConsecutiveCalls(
                    'abc',
                    'def'
                );

        $result = $this->invokeMethod($handler, 'mapObjectsToEntities', $objects);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getEntityKey method.
     * @throws ReflectionException
     * @covers ::getEntityKey
     */
    public function testGetEntityKey(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = 'abc|def';

        /* @var GenericEntity&MockObject $entity */
        $entity = $this->createMock(GenericEntity::class);
        $entity->expects($this->once())
               ->method('getType')
               ->willReturn($type);
        $entity->expects($this->once())
               ->method('getName')
               ->willReturn($name);

        $handler = new GenericDetailsHandler(
            $this->itemRepository,
            $this->machineRepository,
            $this->mapperManager,
            $this->recipeRepository
        );
        $result = $this->invokeMethod($handler, 'getEntityKey', $entity);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        $entities = [
            $this->createMock(GenericEntity::class),
            $this->createMock(GenericEntity::class),
        ];

        $expectedResult = new GenericDetailsResponse();
        $expectedResult->setEntities($entities);

        $handler = new GenericDetailsHandler(
            $this->itemRepository,
            $this->machineRepository,
            $this->mapperManager,
            $this->recipeRepository
        );
        $result = $this->invokeMethod($handler, 'createResponse', $entities);

        $this->assertEquals($expectedResult, $result);
    }
}
