<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\Machine as ClientMachine;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseMachineToClientMachineMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DatabaseMachineToClientMachineMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Mapper\DatabaseMachineToClientMachineMapper
 */
class DatabaseMachineToClientMachineMapperTest extends TestCase
{
    /** @var TranslationService&MockObject */
    private TranslationService $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DatabaseMachineToClientMachineMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): DatabaseMachineToClientMachineMapper
    {
        return $this->getMockBuilder(DatabaseMachineToClientMachineMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->translationService,
                    ])
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(DatabaseMachine::class, $instance->getSupportedSourceClass());
        $this->assertSame(ClientMachine::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $source = new DatabaseMachine();
        $source->setName('abc')
               ->setCraftingSpeed(13.37)
               ->setNumberOfItemSlots(12)
               ->setNumberOfFluidInputSlots(23)
               ->setNumberOfFluidOutputSlots(34)
               ->setNumberOfModuleSlots(45)
               ->setEnergyUsage(73.31)
               ->setEnergyUsageUnit('def');

        $expectedDestination = new ClientMachine();
        $expectedDestination->name = 'abc';
        $expectedDestination->craftingSpeed = 13.37;
        $expectedDestination->numberOfItemSlots = 12;
        $expectedDestination->numberOfFluidInputSlots = 23;
        $expectedDestination->numberOfFluidOutputSlots = 34;
        $expectedDestination->numberOfModuleSlots = 45;
        $expectedDestination->energyUsage = 73.31;
        $expectedDestination->energyUsageUnit = 'def';

        $destination = new ClientMachine();

        $instance = $this->createInstance(['addToTranslationService']);
        $instance->expects($this->once())
                 ->method('addToTranslationService')
                 ->with($this->equalTo($expectedDestination));

        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
