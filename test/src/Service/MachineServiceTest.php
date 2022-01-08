<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FactorioItemBrowser\Api\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Entity\RecipeIngredient;
use FactorioItemBrowser\Api\Database\Entity\RecipeProduct;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Server\Service\MachineService;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Common\Constant\ItemType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the MachineService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Service\MachineService
 */
class MachineServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var MachineRepository&MockObject */
    private MachineRepository $machineRepository;

    protected function setUp(): void
    {
        $this->machineRepository = $this->createMock(MachineRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return MachineService&MockObject
     */
    private function createInstance(array $mockedMethods = []): MachineService
    {
        return $this->getMockBuilder(MachineService::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->machineRepository,
                    ])
                    ->getMock();
    }

    public function testGetMachinesByCraftingCategory(): void
    {
        $craftingCategoryName = 'abc';

        $machines = [
            $this->createMock(Machine::class),
            $this->createMock(Machine::class),
        ];

        $combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');

        $craftingCategory = new CraftingCategory();
        $craftingCategory->setName($craftingCategoryName);

        $this->machineRepository->expects($this->once())
                                ->method('findByCraftingCategoryName')
                                ->with(
                                    $this->identicalTo($combinationId),
                                    $this->identicalTo($craftingCategoryName)
                                )
                                ->willReturn($machines);

        $instance = $this->createInstance();
        $result = $instance->getMachinesByCraftingCategory($combinationId, $craftingCategory);

        $this->assertSame($machines, $result);
    }

    public function testFilterMachinesForRecipe(): void
    {
        $numberOfItems = 42;
        $numberOfInputFluids = 1337;
        $numberOfOutputFluids = 21;

        $machine1 = $this->createMock(Machine::class);
        $machine2 = $this->createMock(Machine::class);
        $machine3 = $this->createMock(Machine::class);

        $machines = [$machine1, $machine2, $machine3];
        $expectedResult = [$machine1, $machine3];

        $ingredients = $this->createMock(Collection::class);
        $products = $this->createMock(Collection::class);

        $recipe = $this->createMock(Recipe::class);
        $recipe->expects($this->exactly(2))
               ->method('getIngredients')
               ->willReturn($ingredients);
        $recipe->expects($this->once())
               ->method('getProducts')
               ->willReturn($products);

        $instance = $this->createInstance(['countItemType', 'isMachineValid']);
        $instance->expects($this->exactly(3))
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
        $instance->expects($this->exactly(3))
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

        $result = $instance->filterMachinesForRecipe($machines, $recipe);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCountItemType(): void
    {
        $type = 'abc';

        $entity1 = $this->createMock(RecipeIngredient::class);
        $entity2 = $this->createMock(RecipeIngredient::class);
        $entity3 = $this->createMock(RecipeIngredient::class);
        $entity4 = $this->createMock(RecipeIngredient::class);

        $entities = new ArrayCollection([$entity1, $entity2, $entity3, $entity4]);
        $expectedResult = 2;

        $instance = $this->createInstance(['getItemType']);
        $instance->expects($this->exactly(4))
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

        $result = $this->invokeMethod($instance, 'countItemType', $entities, $type);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideGetItemType(): array
    {
        $item = new Item();
        $item->setType('abc');

        $ingredient = new RecipeIngredient();
        $ingredient->setItem($item);

        $product = new RecipeProduct();
        $product->setItem($item);

        return [
            [$ingredient, 'abc'],
            [$product, 'abc'],
            [$item, null],
        ];
    }

    /**
     * @param object $entity
     * @param string|null $expectedResult
     * @throws ReflectionException
     * @dataProvider provideGetItemType
     */
    public function testGetItemType(object $entity, ?string $expectedResult): void
    {
        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getItemType', $entity);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideIsMachineValid(): array
    {
        $machine = new Machine();
        $machine->setNumberOfItemSlots(10)
                ->setNumberOfFluidInputSlots(20)
                ->setNumberOfFluidOutputSlots(30);

        $character = new Machine();
        $character->setNumberOfItemSlots(Machine::VALUE_UNLIMITED_SLOTS)
               ->setNumberOfFluidInputSlots(20)
               ->setNumberOfFluidOutputSlots(30);

        return [
            [$machine, 5, 5, 5, true],
            [$machine, 10, 20, 30, true],
            [$machine, 15, 5, 5, false], // Too many items
            [$machine, 5, 25, 5, false], // Too many input fluids
            [$machine, 5, 5, 35, false], // Too many output fluids

            [$character, 1337, 5, 5, true], // Unlimited items
            [$character, 5, 25, 5, false], // Too many input fluids
            [$character, 5, 5, 35, false], // Too many output fluids
        ];
    }

    /**
     * @param Machine $machine
     * @param int $numberOfItems
     * @param int $numberOfFluidInputs
     * @param int $numberOfFluidOutputs
     * @param bool $expectedResult
     * @throws ReflectionException
     * @dataProvider provideIsMachineValid
     */
    public function testIsMachineValid(
        Machine $machine,
        int $numberOfItems,
        int $numberOfFluidInputs,
        int $numberOfFluidOutputs,
        bool $expectedResult
    ): void {
        $instance = $this->createInstance();

        $result = $this->invokeMethod(
            $instance,
            'isMachineValid',
            $machine,
            $numberOfItems,
            $numberOfFluidInputs,
            $numberOfFluidOutputs
        );

        $this->assertSame($expectedResult, $result);
    }

    public function testSortMachines(): void
    {
        $machine1 = $this->createMock(Machine::class);
        $machine2 = $this->createMock(Machine::class);

        $machines = [$machine1, $machine2];
        $expectedResult = [$machine2, $machine1];

        $instance = $this->createInstance(['compareMachines']);
        $instance->expects($this->once())
                 ->method('compareMachines')
                 ->with($this->identicalTo($machine1), $this->identicalTo($machine2))
                 ->willReturn(1);

        $result = $instance->sortMachines($machines);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideCompareMachines(): array
    {
        $machine1 = new Machine();
        $machine1->setName('abc');

        $machine2 = new Machine();
        $machine2->setName('zyx');

        $character = new Machine();
        $character->setName(Constant::ENTITY_NAME_CHARACTER);

        return [
            [$machine1, $machine2, -1],
            [$machine2, $machine1, 1],
            [$machine1, $machine1, 0],

            [$character, $machine1, -1],
            [$character, $machine2, -1],
            [$machine1, $character, 1],
            [$machine2, $character, 1],
        ];
    }

    /**
     * @param Machine $left
     * @param Machine $right
     * @param int $expectedResult
     * @throws ReflectionException
     * @dataProvider provideCompareMachines
     */
    public function testCompareMachines(Machine $left, Machine $right, int $expectedResult): void
    {
        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'compareMachines', $left, $right);

        $this->assertSame($expectedResult, $result);
    }
}
