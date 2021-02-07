<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseItemToGenericEntityMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DatabaseItemToGenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Mapper\DatabaseItemToGenericEntityMapper
 */
class DatabaseItemToGenericEntityMapperTest extends TestCase
{
    /** @var TranslationService&MockObject */
    private TranslationService $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DatabaseItemToGenericEntityMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): DatabaseItemToGenericEntityMapper
    {
        return $this->getMockBuilder(DatabaseItemToGenericEntityMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->translationService,
                    ])
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideSupports(): array
    {
        $databaseItem = $this->createMock(DatabaseItem::class);
        $genericEntity = $this->createMock(GenericEntity::class);
        $genericEntityWithRecipes = $this->createMock(GenericEntityWithRecipes::class);

        return [
            [$databaseItem, $genericEntity, true],
            [$databaseItem, $genericEntityWithRecipes, true],
            [$genericEntity, $genericEntity, false],
        ];
    }

    /**
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $instance = new DatabaseItemToGenericEntityMapper($this->translationService);
        $result = $instance->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    public function testMap(): void
    {
        $source = new DatabaseItem();
        $source->setType('abc')
               ->setName('def');

        $expectedDestination = new GenericEntity();
        $expectedDestination->type = 'abc';
        $expectedDestination->name = 'def';

        $destination = new GenericEntity();

        $instance = $this->createInstance(['addToTranslationService']);
        $instance->expects($this->once())
                 ->method('addToTranslationService')
                 ->with($this->equalTo($expectedDestination));

        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
