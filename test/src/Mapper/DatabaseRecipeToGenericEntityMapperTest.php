<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseRecipeToGenericEntityMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DatabaseRecipeToGenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Mapper\DatabaseRecipeToGenericEntityMapper
 */
class DatabaseRecipeToGenericEntityMapperTest extends TestCase
{
    /** @var TranslationService&MockObject */
    private TranslationService $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DatabaseRecipeToGenericEntityMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): DatabaseRecipeToGenericEntityMapper
    {
        return $this->getMockBuilder(DatabaseRecipeToGenericEntityMapper::class)
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
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);
        $genericEntity = $this->createMock(GenericEntity::class);
        $genericEntityWithRecipes = $this->createMock(GenericEntityWithRecipes::class);
        $clientRecipe = $this->createMock(ClientRecipe::class);

        return [
            [$databaseRecipe, $genericEntity, true],
            [$databaseRecipe, $genericEntityWithRecipes, true],
            [$databaseRecipe, $clientRecipe, false],
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
        $instance = $this->createInstance();
        $result = $instance->supports($source, $destination);

        $this->assertSame($expectedResult, $result);
    }

    public function testMap(): void
    {
        $source = new DatabaseRecipe();
        $source->setName('abc');

        $expectedDestination = new GenericEntity();
        $expectedDestination->type = EntityType::RECIPE;
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
