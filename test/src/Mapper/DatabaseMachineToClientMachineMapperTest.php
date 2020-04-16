<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Machine as ClientMachine;
use FactorioItemBrowser\Api\Database\Entity\Machine as DatabaseMachine;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseMachineToClientMachineMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DatabaseMachineToClientMachineMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\DatabaseMachineToClientMachineMapper
 */
class DatabaseMachineToClientMachineMapperTest extends TestCase
{
    /**
     * The mocked translation service.
     * @var TranslationService&MockObject
     */
    protected $translationService;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = DatabaseMachine::class;

        $mapper = new DatabaseMachineToClientMachineMapper($this->translationService);
        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = ClientMachine::class;

        $mapper = new DatabaseMachineToClientMachineMapper($this->translationService);
        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @throws ReflectionException
     * @covers ::map
     */
    public function testMap(): void
    {
        /* @var DatabaseMachine&MockObject $databaseMachine */
        $databaseMachine = $this->createMock(DatabaseMachine::class);
        $databaseMachine->expects($this->once())
                        ->method('getName')
                        ->willReturn('abc');
        $databaseMachine->expects($this->once())
                        ->method('getCraftingSpeed')
                        ->willReturn(13.37);
        $databaseMachine->expects($this->once())
                        ->method('getNumberOfItemSlots')
                        ->willReturn(12);
        $databaseMachine->expects($this->once())
                        ->method('getNumberOfFluidInputSlots')
                        ->willReturn(23);
        $databaseMachine->expects($this->once())
                        ->method('getNumberOfFluidOutputSlots')
                        ->willReturn(34);
        $databaseMachine->expects($this->once())
                        ->method('getNumberOfModuleSlots')
                        ->willReturn(45);
        $databaseMachine->expects($this->once())
                        ->method('getEnergyUsage')
                        ->willReturn(73.31);
        $databaseMachine->expects($this->once())
                        ->method('getEnergyUsageUnit')
                        ->willReturn('def');

        /* @var ClientMachine&MockObject $clientMachine */
        $clientMachine = $this->createMock(ClientMachine::class);
        $clientMachine->expects($this->once())
                      ->method('setName')
                      ->with($this->identicalTo('abc'))
                      ->willReturnSelf();
        $clientMachine->expects($this->once())
                      ->method('setCraftingSpeed')
                      ->with($this->identicalTo(13.37))
                      ->willReturnSelf();
        $clientMachine->expects($this->once())
                      ->method('setNumberOfItemSlots')
                      ->with($this->identicalTo(12))
                      ->willReturnSelf();
        $clientMachine->expects($this->once())
                      ->method('setNumberOfFluidInputSlots')
                      ->with($this->identicalTo(23))
                      ->willReturnSelf();
        $clientMachine->expects($this->once())
                      ->method('setNumberOfFluidOutputSlots')
                      ->with($this->identicalTo(34))
                      ->willReturnSelf();
        $clientMachine->expects($this->once())
                      ->method('setNumberOfModuleSlots')
                      ->with($this->identicalTo(45))
                      ->willReturnSelf();
        $clientMachine->expects($this->once())
                      ->method('setEnergyUsage')
                      ->with($this->identicalTo(73.31))
                      ->willReturnSelf();
        $clientMachine->expects($this->once())
                      ->method('setEnergyUsageUnit')
                      ->with($this->identicalTo('def'))
                      ->willReturnSelf();

        /* @var DatabaseMachineToClientMachineMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseMachineToClientMachineMapper::class)
                       ->onlyMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($clientMachine));

        $mapper->map($databaseMachine, $clientMachine);
    }
}
