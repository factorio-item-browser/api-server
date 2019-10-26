<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Server\Mapper\RecipeDataToGenericEntityMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeDataToGenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\RecipeDataToGenericEntityMapper
 */
class RecipeDataToGenericEntityMapperTest extends TestCase
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
        $expectedResult = RecipeData::class;

        $mapper = new RecipeDataToGenericEntityMapper($this->translationService);
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

        $mapper = new RecipeDataToGenericEntityMapper($this->translationService);
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
        /* @var RecipeData&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeData::class);
        $recipeData->expects($this->once())
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

        /* @var RecipeDataToGenericEntityMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(RecipeDataToGenericEntityMapper::class)
                       ->onlyMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($genericEntity));

        $mapper->map($recipeData, $genericEntity);
    }
}
