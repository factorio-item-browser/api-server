<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseRecipeToGenericEntityMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DatabaseRecipeToGenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\DatabaseRecipeToGenericEntityMapper
 */
class DatabaseRecipeToGenericEntityMapperTest extends TestCase
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
        /* @var DatabaseRecipe&MockObject $databaseRecipe */
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);
        /* @var GenericEntity&MockObject $genericEntity */
        $genericEntity = $this->createMock(GenericEntity::class);
        /* @var GenericEntityWithRecipes&MockObject $genericEntityWithRecipes */
        $genericEntityWithRecipes = $this->createMock(GenericEntityWithRecipes::class);
        /* @var ClientRecipe&MockObject $clientRecipe */
        $clientRecipe = $this->createMock(ClientRecipe::class);

        return [
            [$databaseRecipe, $genericEntity, true],
            [$databaseRecipe, $genericEntityWithRecipes, true],
            [$databaseRecipe, $clientRecipe, false],
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
        $mapper = new DatabaseRecipeToGenericEntityMapper($this->translationService);
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
        /* @var DatabaseRecipe&MockObject $databaseRecipe */
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);
        $databaseRecipe->expects($this->once())
                       ->method('getName')
                       ->willReturn('abc');

        /* @var GenericEntity&MockObject $genericEntity */
        $genericEntity = $this->createMock(GenericEntity::class);
        $genericEntity->expects($this->once())
                      ->method('setType')
                      ->with($this->identicalTo(EntityType::RECIPE))
                      ->willReturnSelf();
        $genericEntity->expects($this->once())
                      ->method('setName')
                      ->with($this->identicalTo('abc'))
                      ->willReturnSelf();

        /* @var DatabaseRecipeToGenericEntityMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseRecipeToGenericEntityMapper::class)
                       ->onlyMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($genericEntity));

        $mapper->map($databaseRecipe, $genericEntity);
    }
}
