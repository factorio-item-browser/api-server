<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseMachineToGenericEntityMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the MachineDataToGenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\DatabaseMachineToGenericEntityMapper
 */
class DatabaseMachineToGenericEntityMapperTest extends TestCase
{
    /**
     * The mocked translation service.
     * @var TranslationService&MockObject
     */
    protected $translationService;

    /**
     * Sets up the test case.
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
        $expectedResult = Machine::class;

        $mapper = new DatabaseMachineToGenericEntityMapper($this->translationService);
        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = GenericEntity::class;

        $mapper = new DatabaseMachineToGenericEntityMapper($this->translationService);
        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        /* @var Machine&MockObject $machine */
        $machine = $this->createMock(Machine::class);
        $machine->expects($this->once())
                ->method('getName')
                ->willReturn('abc');

        /* @var GenericEntity&MockObject $genericEntity */
        $genericEntity = $this->createMock(GenericEntity::class);
        $genericEntity->expects($this->once())
                      ->method('setType')
                      ->with($this->identicalTo(EntityType::MACHINE))
                      ->willReturnSelf();
        $genericEntity->expects($this->once())
                      ->method('setName')
                      ->with($this->identicalTo('abc'))
                      ->willReturnSelf();

        /* @var DatabaseMachineToGenericEntityMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseMachineToGenericEntityMapper::class)
                       ->onlyMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($genericEntity));

        $mapper->map($machine, $genericEntity);
    }
}
