<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\MachineService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericDetailsHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct()
    {
        /* @var ItemService $itemService */
        $itemService = $this->createMock(ItemService::class);
        /* @var MachineService $machineService */
        $machineService = $this->createMock(MachineService::class);
        /* @var RecipeService $recipeService */
        $recipeService = $this->createMock(RecipeService::class);
        /* @var TranslationService $translationService */
        $translationService = $this->createMock(TranslationService::class);

        $handler = new GenericDetailsHandler($itemService, $machineService, $recipeService, $translationService);
        $this->assertSame($itemService, $this->extractProperty($handler, 'itemService'));
        $this->assertSame($machineService, $this->extractProperty($handler, 'machineService'));
        $this->assertSame($recipeService, $this->extractProperty($handler, 'recipeService'));
        $this->assertSame($translationService, $this->extractProperty($handler, 'translationService'));
    }

    /**
     * Tests the handleRequest method.
     * @covers ::handleRequest
     */
    public function testHandleRequest()
    {
        $entity1 = new GenericEntity();
        $entity2 = new GenericEntity();
        $entity3 = new GenericEntity();
        $entity4 = new GenericEntity();
        $entity5 = new GenericEntity();
        $entity6 = new GenericEntity();

        $requestData = new DataContainer(['abc' => 'def']);
        $namesByTypes = [
            EntityType::FLUID => ['ghi'],
            EntityType::ITEM => ['jkl'],
            EntityType::MACHINE => ['mno'],
            EntityType::RECIPE => ['pqr'],
        ];
        $expectedResult = [
            'entities' => [
                $entity1,
                $entity2,
                $entity3,
                $entity4,
                $entity5,
                $entity6,
            ]
        ];

        /* @var ItemService $itemService */
        $itemService = $this->createMock(ItemService::class);
        /* @var MachineService $machineService */
        $machineService = $this->createMock(MachineService::class);
        /* @var RecipeService $recipeService */
        $recipeService = $this->createMock(RecipeService::class);

        /* @var TranslationService|MockObject $translationService */
        $translationService = $this->getMockBuilder(TranslationService::class)
                                   ->setMethods(['translateEntities'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $translationService->expects($this->once())
                           ->method('translateEntities');

        /* @var GenericDetailsHandler|MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->setMethods(['getEntityNamesByType', 'handleRecipes', 'handleMachines', 'handleItems'])
                        ->setConstructorArgs([$itemService, $machineService, $recipeService, $translationService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getEntityNamesByType')
                ->with($requestData)
                ->willReturn($namesByTypes);
        $handler->expects($this->once())
                ->method('handleRecipes')
                ->with(['pqr'])
                ->willReturn([$entity1, $entity2]);
        $handler->expects($this->once())
                ->method('handleMachines')
                ->with(['mno'])
                ->willReturn([$entity3, $entity4]);
        $handler->expects($this->once())
                ->method('handleItems')
                ->with([EntityType::FLUID => ['ghi'], EntityType::ITEM => ['jkl']])
                ->willReturn([$entity5, $entity6]);

        $result = $this->invokeMethod($handler, 'handleRequest', $requestData);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the handleRecipes method.
     * @covers ::handleRecipes
     */
    public function testHandleRecipes()
    {
        $recipeNames = ['abc', 'def', 'ghi'];
        $availableRecipeNames = ['def', 'ghi'];
        $entity1 = new GenericEntity();
        $entity2 = new GenericEntity();
        $expectedResult = [$entity1, $entity2];

        /* @var ItemService $itemService */
        $itemService = $this->createMock(ItemService::class);
        /* @var MachineService $machineService */
        $machineService = $this->createMock(MachineService::class);
        /* @var TranslationService $translationService */
        $translationService = $this->createMock(TranslationService::class);

        /* @var RecipeService|MockObject $recipeService */
        $recipeService = $this->getMockBuilder(RecipeService::class)
                              ->setMethods(['filterAvailableNames'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $recipeService->expects($this->once())
                      ->method('filterAvailableNames')
                      ->with($recipeNames)
                      ->willReturn($availableRecipeNames);

        /* @var GenericDetailsHandler|MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->setMethods(['createGenericEntity'])
                        ->setConstructorArgs([$itemService, $machineService, $recipeService, $translationService])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('createGenericEntity')
                ->withConsecutive(
                    [EntityType::RECIPE, 'def'],
                    [EntityType::RECIPE, 'ghi']
                )
                ->willReturnOnConsecutiveCalls(
                    $entity1,
                    $entity2
                );

        $result = $this->invokeMethod($handler, 'handleRecipes', $recipeNames);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the handleMachines method.
     * @covers ::handleMachines
     */
    public function testHandleMachines()
    {
        $machineNames = ['abc', 'def', 'ghi'];
        $availableMachineNames = ['def', 'ghi'];
        $entity1 = new GenericEntity();
        $entity2 = new GenericEntity();
        $expectedResult = [$entity1, $entity2];

        /* @var ItemService $itemService */
        $itemService = $this->createMock(ItemService::class);
        /* @var RecipeService $recipeService */
        $recipeService = $this->createMock(RecipeService::class);
        /* @var TranslationService $translationService */
        $translationService = $this->createMock(TranslationService::class);

        /* @var MachineService|MockObject $machineService */
        $machineService = $this->getMockBuilder(MachineService::class)
                              ->setMethods(['filterAvailableNames'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $machineService->expects($this->once())
                      ->method('filterAvailableNames')
                      ->with($machineNames)
                      ->willReturn($availableMachineNames);

        /* @var GenericDetailsHandler|MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->setMethods(['createGenericEntity'])
                        ->setConstructorArgs([$itemService, $machineService, $recipeService, $translationService])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('createGenericEntity')
                ->withConsecutive(
                    [EntityType::MACHINE, 'def'],
                    [EntityType::MACHINE, 'ghi']
                )
                ->willReturnOnConsecutiveCalls(
                    $entity1,
                    $entity2
                );

        $result = $this->invokeMethod($handler, 'handleMachines', $machineNames);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the handleItems method.
     * @covers ::handleItems
     */
    public function testHandleItems()
    {
        $itemNamesByType = ['abc', 'def', 'ghi'];
        $availableItemNamesByTypes = ['abc' => ['def', 'ghi'], 'jkl' => ['mno']];
        $entity1 = new GenericEntity();
        $entity2 = new GenericEntity();
        $entity3 = new GenericEntity();
        $expectedResult = [$entity1, $entity2, $entity3];

        /* @var MachineService $machineService */
        $machineService = $this->createMock(MachineService::class);
        /* @var RecipeService $recipeService */
        $recipeService = $this->createMock(RecipeService::class);
        /* @var TranslationService $translationService */
        $translationService = $this->createMock(TranslationService::class);

        /* @var ItemService|MockObject $itemService */
        $itemService = $this->getMockBuilder(ItemService::class)
                              ->setMethods(['filterAvailableTypesAndNames'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $itemService->expects($this->once())
                      ->method('filterAvailableTypesAndNames')
                      ->with($itemNamesByType)
                      ->willReturn($availableItemNamesByTypes);

        /* @var GenericDetailsHandler|MockObject $handler */
        $handler = $this->getMockBuilder(GenericDetailsHandler::class)
                        ->setMethods(['createGenericEntity'])
                        ->setConstructorArgs([$itemService, $machineService, $recipeService, $translationService])
                        ->getMock();
        $handler->expects($this->exactly(3))
                ->method('createGenericEntity')
                ->withConsecutive(
                    ['abc', 'def'],
                    ['abc', 'ghi'],
                    ['jkl', 'mno']
                )
                ->willReturnOnConsecutiveCalls(
                    $entity1,
                    $entity2,
                    $entity3
                );

        $result = $this->invokeMethod($handler, 'handleItems', $itemNamesByType);
        $this->assertSame($expectedResult, $result);
    }
    
    /**
     * Tests the createGenericEntity method.
     * @covers ::createGenericEntity
     */
    public function testCreateGenericEntity()
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = new GenericEntity();
        $expectedResult->setType($type)
                       ->setName($name);

        /* @var ItemService $itemService */
        $itemService = $this->createMock(ItemService::class);
        /* @var MachineService $machineService */
        $machineService = $this->createMock(MachineService::class);
        /* @var RecipeService $recipeService */
        $recipeService = $this->createMock(RecipeService::class);

        /* @var TranslationService|MockObject $translationService */
        $translationService = $this->getMockBuilder(TranslationService::class)
                                   ->setMethods(['addEntityToTranslate'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $translationService->expects($this->once())
                           ->method('addEntityToTranslate')
                           ->with($this->callback(function ($param) use ($expectedResult) {
                               $this->assertEquals($expectedResult, $param);
                               return true;
                           }));

        $handler = new GenericDetailsHandler($itemService, $machineService, $recipeService, $translationService);
        $result = $this->invokeMethod($handler, 'createGenericEntity', $type, $name);
        $this->assertEquals($expectedResult, $result);
    }
}
