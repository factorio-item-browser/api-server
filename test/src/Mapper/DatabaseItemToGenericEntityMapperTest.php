<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseItemToGenericEntityMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DatabaseItemToGenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\DatabaseItemToGenericEntityMapper
 */
class DatabaseItemToGenericEntityMapperTest extends TestCase
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
     * Provides the data for the supports test.
     * @return array
     * @throws ReflectionException
     */
    public function provideSupports(): array
    {
        /* @var DatabaseItem&MockObject $databaseItem */
        $databaseItem = $this->createMock(DatabaseItem::class);
        /* @var GenericEntity&MockObject $genericEntity */
        $genericEntity = $this->createMock(GenericEntity::class);
        /* @var GenericEntityWithRecipes&MockObject $genericEntityWithRecipes */
        $genericEntityWithRecipes = $this->createMock(GenericEntityWithRecipes::class);

        return [
            [$databaseItem, $genericEntity, true],
            [$databaseItem, $genericEntityWithRecipes, true],
            [$genericEntity, $genericEntity, false],
        ];
    }

    /**
     * Tests the supports method.
     * @param object $source
     * @param object $destination
     * @param bool $expectedResult
     * @covers ::supports
     * @dataProvider provideSupports
     */
    public function testSupports(object $source, object $destination, bool $expectedResult): void
    {
        $mapper = new DatabaseItemToGenericEntityMapper($this->translationService);
        $result = $mapper->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @throws ReflectionException
     * @covers ::map
     */
    public function testMap(): void
    {
        /* @var DatabaseItem&MockObject $databaseItem */
        $databaseItem = $this->createMock(DatabaseItem::class);
        $databaseItem->expects($this->once())
                       ->method('getType')
                       ->willReturn('abc');
        $databaseItem->expects($this->once())
                       ->method('getName')
                       ->willReturn('def');

        /* @var GenericEntity&MockObject $genericEntity */
        $genericEntity = $this->createMock(GenericEntity::class);
        $genericEntity->expects($this->once())
                      ->method('setType')
                      ->with($this->identicalTo('abc'))
                      ->willReturnSelf();
        $genericEntity->expects($this->once())
                      ->method('setName')
                      ->with($this->identicalTo('def'))
                      ->willReturnSelf();

        /* @var DatabaseItemToGenericEntityMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseItemToGenericEntityMapper::class)
                       ->setMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($genericEntity));

        $mapper->map($databaseItem, $genericEntity);
    }
}