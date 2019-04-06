<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FactorioItemBrowser\Api\Database\Data\MachineData;
use FactorioItemBrowser\Api\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Entity\RecipeIngredient;
use FactorioItemBrowser\Api\Database\Entity\RecipeProduct;
use FactorioItemBrowser\Api\Database\Filter\DataFilter;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Service\MachineService;
use FactorioItemBrowser\Common\Constant\ItemType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MachineService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\MachineService
 */
class MachineServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked data filter.
     * @var DataFilter&MockObject
     */
    protected $dataFilter;

    /**
     * The mocked machine repository.
     * @var MachineRepository&MockObject
     */
    protected $machineRepository;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dataFilter = $this->createMock(DataFilter::class);
        $this->machineRepository = $this->createMock(MachineRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new MachineService($this->dataFilter, $this->machineRepository);

        $this->assertSame($this->dataFilter, $this->extractProperty($service, 'dataFilter'));
        $this->assertSame($this->machineRepository, $this->extractProperty($service, 'machineRepository'));
    }

    /**
     * Tests the getMachinesByCraftingCategory method.
     * @throws ReflectionException
     * @covers ::getMachinesByCraftingCategory
     */
    public function testGetMachinesByCraftingCategory(): void
    {
        $craftingCategoryName = 'abc';
        $enabledModCombinationIds = [42, 1337];
        $machineData = [
            $this->createMock(MachineData::class),
            $this->createMock(MachineData::class),
        ];
        $machineIds = [21, 7331];
        $machines = [
            $this->createMock(Machine::class),
            $this->createMock(Machine::class),
        ];
        
        /* @var CraftingCategory&MockObject $craftingCategory */
        $craftingCategory = $this->createMock(CraftingCategory::class);
        $craftingCategory->expects($this->once())
                         ->method('getName')
                         ->willReturn($craftingCategoryName);
        
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);
        
        $this->machineRepository->expects($this->once())
                                ->method('findDataByCraftingCategories')
                                ->with(
                                    $this->identicalTo([$craftingCategoryName]),
                                    $this->identicalTo($enabledModCombinationIds)
                                )
                                ->willReturn($machineData);
        $this->machineRepository->expects($this->once())
                                ->method('findByIds')
                                ->with($this->identicalTo($machineIds))
                                ->willReturn($machines);

        /* @var MachineService&MockObject $service */
        $service = $this->getMockBuilder(MachineService::class)
                        ->setMethods(['extractIdsFromMachineData'])
                        ->setConstructorArgs([$this->dataFilter, $this->machineRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('extractIdsFromMachineData')
                ->with($this->identicalTo($machineData))
                ->willReturn($machineIds);

        $result = $service->getMachinesByCraftingCategory($craftingCategory, $authorizationToken);

        $this->assertSame($machines, $result);
    }

    /**
     * Tests the extractIdsFromMachineData method.
     * @throws ReflectionException
     * @covers ::extractIdsFromMachineData
     */
    public function testExtractIdsFromMachineData(): void
    {
        /* @var MachineData&MockObject $machineData1 */
        $machineData1 = $this->createMock(MachineData::class);
        $machineData1->expects($this->once())
                     ->method('getId')
                     ->willReturn(42);

        /* @var MachineData&MockObject $machineData2 */
        $machineData2 = $this->createMock(MachineData::class);
        $machineData2->expects($this->once())
                     ->method('getId')
                     ->willReturn(1337);

        $machineData = [
            $this->createMock(MachineData::class),
            $this->createMock(MachineData::class),
        ];
        $filteredMachineData = [$machineData1, $machineData2];
        $expectedResult = [42, 1337];

        $this->dataFilter->expects($this->once())
                         ->method('filter')
                         ->with($this->identicalTo($machineData))
                         ->willReturn($filteredMachineData);

        $service = new MachineService($this->dataFilter, $this->machineRepository);
        $result = $this->invokeMethod($service, 'extractIdsFromMachineData', $machineData);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the filterMachinesForRecipe method.
     * @throws ReflectionException
     * @covers ::filterMachinesForRecipe
     */
    public function testFilterMachinesForRecipe(): void
    {
        $numberOfItems = 42;
        $numberOfInputFluids = 1337;
        $numberOfOutputFluids = 21;

        /* @var Machine&MockObject $machine1 */
        $machine1 = $this->createMock(Machine::class);
        /* @var Machine&MockObject $machine2 */
        $machine2 = $this->createMock(Machine::class);
        /* @var Machine&MockObject $machine3 */
        $machine3 = $this->createMock(Machine::class);

        $machines = [$machine1, $machine2, $machine3];
        $expectedResult = [$machine1, $machine3];

        /* @var Collection&MockObject $ingredients */
        $ingredients = $this->createMock(Collection::class);
        /* @var Collection&MockObject $products */
        $products = $this->createMock(Collection::class);

        /* @var Recipe&MockObject $recipe */
        $recipe = $this->createMock(Recipe::class);
        $recipe->expects($this->exactly(2))
               ->method('getIngredients')
               ->willReturn($ingredients);
        $recipe->expects($this->once())
               ->method('getProducts')
               ->willReturn($products);

        /* @var MachineService&MockObject $service */
        $service = $this->getMockBuilder(MachineService::class)
                        ->setMethods(['countItemType', 'isMachineValid'])
                        ->setConstructorArgs([$this->dataFilter, $this->machineRepository])
                        ->getMock();
        $service->expects($this->exactly(3))
                ->method('countItemType')
                ->withConsecutive(
                    [$this->identicalTo($ingredients), $this->identicalTo(ItemType::ITEM)],
                    [$this->identicalTo($ingredients), $this->identicalTo(ItemType::FLUID)],
                    [$this->identicalTo($products), $this->identicalTo(ItemType::FLUID)]
                )
                ->willReturnOnConsecutiveCalls(
                    $numberOfItems,
                    $numberOfInputFluids,
                    $numberOfOutputFluids
                );
        $service->expects($this->exactly(3))
                ->method('isMachineValid')
                ->withConsecutive(
                    [
                        $this->identicalTo($machine1),
                        $this->identicalTo($numberOfItems),
                        $this->identicalTo($numberOfInputFluids),
                        $this->identicalTo($numberOfOutputFluids),
                    ],
                    [
                        $this->identicalTo($machine2),
                        $this->identicalTo($numberOfItems),
                        $this->identicalTo($numberOfInputFluids),
                        $this->identicalTo($numberOfOutputFluids),
                    ],
                    [
                        $this->identicalTo($machine3),
                        $this->identicalTo($numberOfItems),
                        $this->identicalTo($numberOfInputFluids),
                        $this->identicalTo($numberOfOutputFluids),
                    ]
                )
                ->willReturnOnConsecutiveCalls(
                    true,
                    false,
                    true
                );

        $result = $service->filterMachinesForRecipe($machines, $recipe);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the countItemType method.
     * @throws ReflectionException
     * @covers ::countItemType
     */
    public function testCountItemType(): void
    {
        $type = 'abc';

        /* @var RecipeIngredient&MockObject $entity1 */
        $entity1 = $this->createMock(RecipeIngredient::class);
        /* @var RecipeIngredient&MockObject $entity2 */
        $entity2 = $this->createMock(RecipeIngredient::class);
        /* @var RecipeIngredient&MockObject $entity3 */
        $entity3 = $this->createMock(RecipeIngredient::class);
        /* @var RecipeIngredient&MockObject $entity4 */
        $entity4 = $this->createMock(RecipeIngredient::class);

        $entities = new ArrayCollection([$entity1, $entity2, $entity3, $entity4]);
        $expectedResult = 2;

        /* @var MachineService&MockObject $service */
        $service = $this->getMockBuilder(MachineService::class)
                        ->setMethods(['getItemType'])
                        ->setConstructorArgs([$this->dataFilter, $this->machineRepository])
                        ->getMock();
        $service->expects($this->exactly(4))
                ->method('getItemType')
                ->withConsecutive(
                    [$this->identicalTo($entity1)],
                    [$this->identicalTo($entity2)],
                    [$this->identicalTo($entity3)],
                    [$this->identicalTo($entity4)]
                )
                ->willReturnOnConsecutiveCalls(
                    $type,
                    'foo',
                    null,
                    $type
                );

        $result = $this->invokeMethod($service, 'countItemType', $entities, $type);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getItemType method with an ingredient as entity.
     * @throws ReflectionException
     * @covers ::getItemType
     */
    public function testGetItemTypeWithIngredient(): void
    {
        $type = 'abc';

        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        $item->expects($this->once())
             ->method('getType')
             ->willReturn($type);

        /* @var RecipeIngredient&MockObject $entity */
        $entity = $this->createMock(RecipeIngredient::class);
        $entity->expects($this->once())
               ->method('getItem')
               ->willReturn($item);

        $service = new MachineService($this->dataFilter, $this->machineRepository);
        $result = $this->invokeMethod($service, 'getItemType', $entity);

        $this->assertSame($type, $result);
    }

    /**
     * Tests the getItemType method with a product as entity.
     * @throws ReflectionException
     * @covers ::getItemType
     */
    public function testGetItemTypeWithProduct(): void
    {
        $type = 'abc';

        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        $item->expects($this->once())
             ->method('getType')
             ->willReturn($type);

        /* @var RecipeProduct&MockObject $entity */
        $entity = $this->createMock(RecipeProduct::class);
        $entity->expects($this->once())
               ->method('getItem')
               ->willReturn($item);

        $service = new MachineService($this->dataFilter, $this->machineRepository);
        $result = $this->invokeMethod($service, 'getItemType', $entity);

        $this->assertSame($type, $result);
    }

    /**
     * Tests the getItemType method with an invalid entity
     * @throws ReflectionException
     * @covers ::getItemType
     */
    public function testGetItemTypeWithInvalidEntity(): void
    {
        $service = new MachineService($this->dataFilter, $this->machineRepository);
        $result = $this->invokeMethod($service, 'getItemType', $this);

        $this->assertNull($result);
    }

    /**
     * Provides the data for the isMachineValid test.
     * @return array
     * @throws ReflectionException
     */
    public function provideIsMachineValid(): array
    {
        /* @var Machine&MockObject $machine */
        $machine = $this->createMock(Machine::class);
        $machine->expects($this->any())
                ->method('getNumberOfItemSlots')
                ->willReturn(10);
        $machine->expects($this->any())
                ->method('getNumberOfFluidInputSlots')
                ->willReturn(20);
        $machine->expects($this->any())
                ->method('getNumberOfFluidOutputSlots')
                ->willReturn(30);

        /* @var Machine&MockObject $player */
        $player = $this->createMock(Machine::class);
        $player->expects($this->any())
               ->method('getNumberOfItemSlots')
               ->willReturn(Machine::VALUE_UNLIMITED_SLOTS);
        $player->expects($this->any())
               ->method('getNumberOfFluidInputSlots')
               ->willReturn(20);
        $player->expects($this->any())
               ->method('getNumberOfFluidOutputSlots')
               ->willReturn(30);


        return [
            [$machine, 5, 5, 5, true],
            [$machine, 10, 20, 30, true],
            [$machine, 15, 5, 5, false], // Too many items
            [$machine, 5, 25, 5, false], // Too many input fluids
            [$machine, 5, 5, 35, false], // Too many output fluids

            [$player, 1337, 5, 5, true], // Unlimited items
            [$player, 5, 25, 5, false], // Too many input fluids
            [$player, 5, 5, 35, false], // Too many output fluids
        ];
    }

    /**
     * Tests the isMachineValid method.
     * @param Machine $machine
     * @param int $numberOfItems
     * @param int $numberOfFluidInputs
     * @param int $numberOfFluidOutputs
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isMachineValid
     * @dataProvider provideIsMachineValid
     */
    public function testIsMachineValid(
        Machine $machine,
        int $numberOfItems,
        int $numberOfFluidInputs,
        int $numberOfFluidOutputs,
        bool $expectedResult
    ): void {
        $service = new MachineService($this->dataFilter, $this->machineRepository);

        $result = $this->invokeMethod(
            $service,
            'isMachineValid',
            $machine,
            $numberOfItems,
            $numberOfFluidInputs,
            $numberOfFluidOutputs
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the sortMachines method.
     * @throws ReflectionException
     * @covers ::sortMachines
     */
    public function testSortMachines(): void
    {
        /* @var Machine&MockObject $machine1 */
        $machine1 = $this->createMock(Machine::class);
        /* @var Machine&MockObject $machine2 */
        $machine2 = $this->createMock(Machine::class);

        $machines = [$machine1, $machine2];
        $expectedResult = [$machine2, $machine1];

        /* @var MachineService&MockObject $service */
        $service = $this->getMockBuilder(MachineService::class)
                        ->setMethods(['compareMachines'])
                        ->setConstructorArgs([$this->dataFilter, $this->machineRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('compareMachines')
                ->with($this->identicalTo($machine1), $this->identicalTo($machine2))
                ->willReturn(1);

        $result = $service->sortMachines($machines);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the compareMachines test.
     * @return array
     * @throws ReflectionException
     */
    public function provideCompareMachines(): array
    {
        /* @var Machine&MockObject $machine1 */
        $machine1 = $this->createMock(Machine::class);
        $machine1->expects($this->any())
                 ->method('getName')
                 ->willReturn('abc');

        /* @var Machine&MockObject $machine2 */
        $machine2 = $this->createMock(Machine::class);
        $machine2->expects($this->any())
                 ->method('getName')
                 ->willReturn('zyx');

        /* @var Machine&MockObject $player */
        $player = $this->createMock(Machine::class);
        $player->expects($this->any())
               ->method('getName')
               ->willReturn('player');

        return [
            [$machine1, $machine2, -1],
            [$machine2, $machine1, 1],
            [$machine1, $machine1, 0],

            [$player, $machine1, -1],
            [$player, $machine2, -1],
            [$machine1, $player, 1],
            [$machine2, $player, 1],
        ];
    }

    /**
     * Tests the compareMachines method.
     * @param Machine $left
     * @param Machine $right
     * @param int $expectedResult
     * @throws ReflectionException
     * @covers ::compareMachines
     * @dataProvider provideCompareMachines
     */
    public function testCompareMachines(Machine $left, Machine $right, int $expectedResult): void
    {
        $service = new MachineService($this->dataFilter, $this->machineRepository);
        $result = $this->invokeMethod($service, 'compareMachines', $left, $right);

        $this->assertSame($expectedResult, $result);
    }
}
