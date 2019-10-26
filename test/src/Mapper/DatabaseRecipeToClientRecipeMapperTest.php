<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Entity\Item as ClientItem;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion as ClientRecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Database\Entity\RecipeIngredient;
use FactorioItemBrowser\Api\Database\Entity\RecipeProduct;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseRecipeToClientRecipeMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the DatabaseRecipeToClientRecipeMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\DatabaseRecipeToClientRecipeMapper
 */
class DatabaseRecipeToClientRecipeMapperTest extends TestCase
{
    use ReflectionTrait;

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
     * Provides the data for the supports test.
     * @return array
     */
    public function provideSupports(): array
    {
        /* @var DatabaseRecipe&MockObject $databaseRecipe */
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);
        /* @var ClientRecipe&MockObject $clientRecipe */
        $clientRecipe = $this->createMock(ClientRecipe::class);
        /* @var ClientRecipeWithExpensiveVersion&MockObject $clientRecipeWithExpensiveVersion */
        $clientRecipeWithExpensiveVersion = $this->createMock(ClientRecipeWithExpensiveVersion::class);
        /* @var GenericEntity&MockObject $genericEntity */
        $genericEntity = $this->createMock(GenericEntity::class);

        return [
            [$databaseRecipe, $clientRecipe, true],
            [$databaseRecipe, $clientRecipeWithExpensiveVersion, true],
            [$clientRecipe, $clientRecipe, false],
            [$databaseRecipe, $genericEntity, false],
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
        $mapper = new DatabaseRecipeToClientRecipeMapper($this->translationService);
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
        /* @var RecipeIngredient&MockObject $databaseIngredient1 */
        $databaseIngredient1 = $this->createMock(RecipeIngredient::class);
        /* @var RecipeIngredient&MockObject $databaseIngredient2 */
        $databaseIngredient2 = $this->createMock(RecipeIngredient::class);
        /* @var RecipeProduct&MockObject $databaseProduct1 */
        $databaseProduct1 = $this->createMock(RecipeProduct::class);
        /* @var RecipeProduct&MockObject $databaseProduct2 */
        $databaseProduct2 = $this->createMock(RecipeProduct::class);

        /* @var DatabaseRecipe&MockObject $databaseRecipe */
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);
        $databaseRecipe->expects($this->once())
                       ->method('getIngredients')
                       ->willReturn(new ArrayCollection([$databaseIngredient1, $databaseIngredient2]));
        $databaseRecipe->expects($this->once())
                       ->method('getProducts')
                       ->willReturn(new ArrayCollection([$databaseProduct1, $databaseProduct2]));

        /* @var ClientRecipe&MockObject $clientRecipe */
        $clientRecipe = $this->createMock(ClientRecipe::class);
        $clientRecipe->expects($this->exactly(2))
                     ->method('addIngredient')
                     ->with($this->isInstanceOf(ClientItem::class));
        $clientRecipe->expects($this->exactly(2))
                     ->method('addProduct')
                     ->with($this->isInstanceOf(ClientItem::class));

        /* @var DatabaseRecipeToClientRecipeMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseRecipeToClientRecipeMapper::class)
                       ->onlyMethods(['mapRecipe', 'mapIngredient', 'mapProduct'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('mapRecipe')
               ->with($this->identicalTo($databaseRecipe), $this->identicalTo($clientRecipe));
        $mapper->expects($this->exactly(2))
               ->method('mapIngredient')
               ->withConsecutive(
                   [$this->identicalTo($databaseIngredient1), $this->isInstanceOf(ClientItem::class)],
                   [$this->identicalTo($databaseIngredient2), $this->isInstanceOf(ClientItem::class)]
               );
        $mapper->expects($this->exactly(2))
               ->method('mapProduct')
               ->withConsecutive(
                   [$this->identicalTo($databaseProduct1), $this->isInstanceOf(ClientItem::class)],
                   [$this->identicalTo($databaseProduct2), $this->isInstanceOf(ClientItem::class)]
               );

        $mapper->map($databaseRecipe, $clientRecipe);
    }

    /**
     * Tests the mapRecipe method.
     * @throws ReflectionException
     * @covers ::mapRecipe
     */
    public function testMapRecipe(): void
    {
        /* @var DatabaseRecipe&MockObject $databaseRecipe */
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);
        $databaseRecipe->expects($this->once())
                       ->method('getName')
                       ->willReturn('abc');
        $databaseRecipe->expects($this->once())
                       ->method('getMode')
                       ->willReturn('def');
        $databaseRecipe->expects($this->once())
                       ->method('getCraftingTime')
                       ->willReturn(13.37);

        /* @var ClientRecipe&MockObject $clientRecipe */
        $clientRecipe = $this->createMock(ClientRecipe::class);
        $clientRecipe->expects($this->once())
                     ->method('setName')
                     ->with($this->identicalTo('abc'))
                     ->willReturnSelf();
        $clientRecipe->expects($this->once())
                     ->method('setMode')
                     ->with($this->identicalTo('def'))
                     ->willReturnSelf();
        $clientRecipe->expects($this->once())
                     ->method('setCraftingTime')
                     ->with($this->identicalTo(13.37))
                     ->willReturnSelf();

        /* @var DatabaseRecipeToClientRecipeMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseRecipeToClientRecipeMapper::class)
                       ->onlyMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($clientRecipe));

        $this->invokeMethod($mapper, 'mapRecipe', $databaseRecipe, $clientRecipe);
    }

    /**
     * Tests the mapIngredient method.
     * @throws ReflectionException
     * @covers ::mapIngredient
     */
    public function testMapIngredient(): void
    {
        /* @var DatabaseItem&MockObject $databaseItem */
        $databaseItem = $this->createMock(DatabaseItem::class);
        $databaseItem->expects($this->once())
                     ->method('getName')
                     ->willReturn('abc');
        $databaseItem->expects($this->once())
                     ->method('getType')
                     ->willReturn('def');

        /* @var RecipeIngredient&MockObject $databaseIngredient */
        $databaseIngredient = $this->createMock(RecipeIngredient::class);
        $databaseIngredient->expects($this->atLeastOnce())
                           ->method('getItem')
                           ->willReturn($databaseItem);
        $databaseIngredient->expects($this->once())
                           ->method('getAmount')
                           ->willReturn(13.37);

        /* @var ClientItem&MockObject $clientIngredient */
        $clientIngredient = $this->createMock(ClientItem::class);
        $clientIngredient->expects($this->once())
                         ->method('setName')
                         ->with($this->identicalTo('abc'))
                         ->willReturnSelf();
        $clientIngredient->expects($this->once())
                         ->method('setType')
                         ->with($this->identicalTo('def'))
                         ->willReturnSelf();
        $clientIngredient->expects($this->once())
                         ->method('setAmount')
                         ->with($this->identicalTo(13.37))
                         ->willReturnSelf();

        /* @var DatabaseRecipeToClientRecipeMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseRecipeToClientRecipeMapper::class)
                       ->onlyMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($clientIngredient));

        $this->invokeMethod($mapper, 'mapIngredient', $databaseIngredient, $clientIngredient);
    }

    /**
     * Tests the mapProduct method.
     * @throws ReflectionException
     * @covers ::mapProduct
     */
    public function testMapProduct(): void
    {
        /* @var DatabaseItem&MockObject $databaseItem */
        $databaseItem = $this->createMock(DatabaseItem::class);
        $databaseItem->expects($this->once())
                     ->method('getName')
                     ->willReturn('abc');
        $databaseItem->expects($this->once())
                     ->method('getType')
                     ->willReturn('def');

        /* @var RecipeProduct&MockObject $databaseProduct */
        $databaseProduct = $this->createMock(RecipeProduct::class);
        $databaseProduct->expects($this->atLeastOnce())
                           ->method('getItem')
                           ->willReturn($databaseItem);
        $databaseProduct->expects($this->once())
                           ->method('getAmount')
                           ->willReturn(13.37);

        /* @var ClientItem&MockObject $clientProduct */
        $clientProduct = $this->createMock(ClientItem::class);
        $clientProduct->expects($this->once())
                         ->method('setName')
                         ->with($this->identicalTo('abc'))
                         ->willReturnSelf();
        $clientProduct->expects($this->once())
                         ->method('setType')
                         ->with($this->identicalTo('def'))
                         ->willReturnSelf();
        $clientProduct->expects($this->once())
                         ->method('setAmount')
                         ->with($this->identicalTo(13.37))
                         ->willReturnSelf();

        /* @var DatabaseRecipeToClientRecipeMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseRecipeToClientRecipeMapper::class)
                       ->onlyMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($clientProduct));

        $this->invokeMethod($mapper, 'mapProduct', $databaseProduct, $clientProduct);
    }
}
