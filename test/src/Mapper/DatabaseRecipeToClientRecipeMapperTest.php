<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\Item as ClientItem;
use FactorioItemBrowser\Api\Client\Transfer\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion as ClientRecipeWithExpensiveVersion;
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
 * @covers \FactorioItemBrowser\Api\Server\Mapper\DatabaseRecipeToClientRecipeMapper
 */
class DatabaseRecipeToClientRecipeMapperTest extends TestCase
{
    use ReflectionTrait;

    /** @var TranslationService&MockObject */
    private TranslationService $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DatabaseRecipeToClientRecipeMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): DatabaseRecipeToClientRecipeMapper
    {
        return $this->getMockBuilder(DatabaseRecipeToClientRecipeMapper::class)
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
        $clientRecipe = $this->createMock(ClientRecipe::class);
        $clientRecipeWithExpensiveVersion = $this->createMock(ClientRecipeWithExpensiveVersion::class);
        $genericEntity = $this->createMock(GenericEntity::class);

        return [
            [$databaseRecipe, $clientRecipe, true],
            [$databaseRecipe, $clientRecipeWithExpensiveVersion, true],
            [$clientRecipe, $clientRecipe, false],
            [$databaseRecipe, $genericEntity, false],
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
        $databaseIngredient1 = $this->createMock(RecipeIngredient::class);
        $databaseIngredient2 = $this->createMock(RecipeIngredient::class);
        $databaseProduct1 = $this->createMock(RecipeProduct::class);
        $databaseProduct2 = $this->createMock(RecipeProduct::class);

        $clientIngredient1 = $this->createMock(ClientItem::class);
        $clientIngredient2 = $this->createMock(ClientItem::class);
        $clientProduct1 = $this->createMock(ClientItem::class);
        $clientProduct2 = $this->createMock(ClientItem::class);

        $source = new DatabaseRecipe();
        $source->setName('abc')
               ->setMode('def')
               ->setCraftingTime(13.37);
        $source->getIngredients()->add($databaseIngredient1);
        $source->getIngredients()->add($databaseIngredient2);
        $source->getProducts()->add($databaseProduct1);
        $source->getProducts()->add($databaseProduct2);

        $expectedDestination = new ClientRecipe();
        $expectedDestination->name = 'abc';
        $expectedDestination->mode = 'def';
        $expectedDestination->craftingTime = 13.37;
        $expectedDestination->ingredients = [$clientIngredient1, $clientIngredient2];
        $expectedDestination->products = [$clientProduct1, $clientProduct2];

        $destination = new ClientRecipe();

        $instance = $this->createInstance(['addToTranslationService', 'createIngredient', 'createProduct']);
        $instance->expects($this->exactly(2))
                 ->method('createIngredient')
                 ->withConsecutive(
                     [$this->identicalTo($databaseIngredient1)],
                     [$this->identicalTo($databaseIngredient2)],
                 )
                 ->willReturnOnConsecutiveCalls(
                     $clientIngredient1,
                     $clientIngredient2
                 );
        $instance->expects($this->exactly(2))
                 ->method('createProduct')
                 ->withConsecutive(
                     [$this->identicalTo($databaseProduct1)],
                     [$this->identicalTo($databaseProduct2)],
                 )
                 ->willReturnOnConsecutiveCalls(
                     $clientProduct1,
                     $clientProduct2
                 );
        $instance->expects($this->once())
                 ->method('addToTranslationService')
                 ->with($this->equalTo($expectedDestination));

        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateIngredient(): void
    {
        $ingredientItem = new DatabaseItem();
        $ingredientItem->setType('abc')
                       ->setName('def');

        $ingredient = new RecipeIngredient();
        $ingredient->setItem($ingredientItem)
                   ->setAmount(13.37);

        $expectedResult = new ClientItem();
        $expectedResult->type = 'abc';
        $expectedResult->name = 'def';
        $expectedResult->amount = 13.37;

        $instance = $this->createInstance(['addToTranslationService']);
        $instance->expects($this->once())
                 ->method('addToTranslationService')
                 ->with($this->equalTo($expectedResult));

        $result = $this->invokeMethod($instance, 'createIngredient', $ingredient);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateProduct(): void
    {
        $productItem = new DatabaseItem();
        $productItem->setType('abc')
                       ->setName('def');

        $product = new RecipeProduct();
        $product->setItem($productItem)
                ->setAmountMin(13.37)
                ->setAmountMax(13.37)
                ->setProbability(1);

        $expectedResult = new ClientItem();
        $expectedResult->type = 'abc';
        $expectedResult->name = 'def';
        $expectedResult->amount = 13.37;

        $instance = $this->createInstance(['addToTranslationService']);
        $instance->expects($this->once())
                 ->method('addToTranslationService')
                 ->with($this->equalTo($expectedResult));

        $result = $this->invokeMethod($instance, 'createProduct', $product);
        $this->assertEquals($expectedResult, $result);
    }
}
