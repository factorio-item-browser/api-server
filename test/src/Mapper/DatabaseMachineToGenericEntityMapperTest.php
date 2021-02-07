<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
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
 * @covers \FactorioItemBrowser\Api\Server\Mapper\DatabaseMachineToGenericEntityMapper
 */
class DatabaseMachineToGenericEntityMapperTest extends TestCase
{
    /** @var TranslationService&MockObject */
    private TranslationService $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DatabaseMachineToGenericEntityMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): DatabaseMachineToGenericEntityMapper
    {
        return $this->getMockBuilder(DatabaseMachineToGenericEntityMapper::class)
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

        $this->assertSame(Machine::class, $instance->getSupportedSourceClass());
        $this->assertSame(GenericEntity::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $source = new Machine();
        $source->setName('abc');

        $expectedDestination = new GenericEntity();
        $expectedDestination->type = EntityType::MACHINE;
        $expectedDestination->name = 'abc';

        $destination = new GenericEntity();

        $instance = $this->createInstance(['addToTranslationService']);
        $instance->expects($this->once())
                 ->method('addToTranslationService')
                 ->with($this->equalTo($expectedDestination));

        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
