<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Server\Mapper\RecipeDataToGenericEntityMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use FactorioItemBrowser\Common\Constant\EntityType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeDataToGenericEntityMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Mapper\RecipeDataToGenericEntityMapper
 */
class RecipeDataToGenericEntityMapperTest extends TestCase
{
    /** @var TranslationService&MockObject */
    private TranslationService $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeDataToGenericEntityMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeDataToGenericEntityMapper
    {
        return $this->getMockBuilder(RecipeDataToGenericEntityMapper::class)
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

        $this->assertSame(RecipeData::class, $instance->getSupportedSourceClass());
        $this->assertSame(GenericEntity::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $source = new RecipeData();
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
