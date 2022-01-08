<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Common\Constant\RecipeMode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeDataCollectionToGenericEntityWithRecipesMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Mapper\RecipeDataCollectionToGenericEntityWithRecipesMapper
 */
class RecipeDataCollectionToGenericEntityWithRecipesMapperTest extends TestCase
{
    use ReflectionTrait;

    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeService&MockObject */
    private RecipeService $recipeService;

    protected function setUp(): void
    {
        $this->recipeService = $this->createMock(RecipeService::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeDataCollectionToGenericEntityWithRecipesMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeDataCollectionToGenericEntityWithRecipesMapper
    {
        $instance = $this->getMockBuilder(RecipeDataCollectionToGenericEntityWithRecipesMapper::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->recipeService
                         ])
                         ->getMock();
        $instance->setMapperManager($this->mapperManager);
        return $instance;
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(RecipeDataCollection::class, $instance->getSupportedSourceClass());
        $this->assertSame(GenericEntityWithRecipes::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $recipeIds = [
            Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1'),
            Uuid::fromString('24db0d5a-a933-4e46-bb5a-0b7d88c6272e'),
        ];
        $databaseRecipes = [
            'abc' => $this->createMock(DatabaseRecipe::class),
            'def' => $this->createMock(DatabaseRecipe::class),
        ];
        $normalData = $this->createMock(RecipeDataCollection::class);
        $expensiveData = $this->createMock(RecipeDataCollection::class);
        $normalRecipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];
        $expensiveRecipes = [
            $this->createMock(RecipeWithExpensiveVersion::class),
            $this->createMock(RecipeWithExpensiveVersion::class),
        ];

        $expectedDestination = new GenericEntityWithRecipes();
        $expectedDestination->recipes = $expensiveRecipes;

        $destination = new GenericEntityWithRecipes();

        $source = $this->createMock(RecipeDataCollection::class);
        $source->expects($this->once())
               ->method('getAllIds')
               ->willReturn($recipeIds);
        $source->expects($this->exactly(2))
               ->method('filterMode')
               ->withConsecutive(
                   [$this->identicalTo(RecipeMode::NORMAL)],
                   [$this->identicalTo(RecipeMode::EXPENSIVE)],
               )
               ->willReturnOnConsecutiveCalls(
                   $normalData,
                   $expensiveData
               );

        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo($recipeIds))
                            ->willReturn($databaseRecipes);

        $instance = $this->createInstance(['mapNormalRecipes', 'mapExpensiveRecipes']);
        $instance->expects($this->once())
                 ->method('mapNormalRecipes')
                 ->with($this->identicalTo($databaseRecipes), $this->identicalTo($normalData))
                 ->willReturn($normalRecipes);
        $instance->expects($this->once())
                 ->method('mapExpensiveRecipes')
                 ->with(
                     $this->identicalTo($databaseRecipes),
                     $this->identicalTo($normalRecipes),
                     $this->identicalTo($expensiveData)
                 )
                 ->willReturn($expensiveRecipes);

        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapNormalRecipes(): void
    {
        $recipeData1 = new RecipeData();
        $recipeData1->setId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'));
        $recipeData2 = new RecipeData();
        $recipeData2->setId(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'));
        $recipeData3 = new RecipeData();
        $recipeData3->setId(Uuid::fromString('34c0f9b6-ec83-45d0-8283-cdfafd9d838a'));

        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getValues')
                   ->willReturn([$recipeData1, $recipeData2, $recipeData3]);

        $databaseRecipe1 = $this->createMock(DatabaseRecipe::class);
        $databaseRecipe2 = $this->createMock(DatabaseRecipe::class);
        $databaseRecipes = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => $databaseRecipe1,
            '34c0f9b6-ec83-45d0-8283-cdfafd9d838a' => $databaseRecipe2,
        ];

        $mappedRecipe1 = new RecipeWithExpensiveVersion();
        $mappedRecipe1->name = 'abc';
        $mappedRecipe2 = new RecipeWithExpensiveVersion();
        $mappedRecipe2->name = 'def';

        $expectedResult = [
            'abc' => $mappedRecipe1,
            'def' => $mappedRecipe2,
        ];

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [
                                    $this->identicalTo($databaseRecipe1),
                                    $this->isInstanceOf(RecipeWithExpensiveVersion::class),
                                ],
                                [
                                    $this->identicalTo($databaseRecipe2),
                                    $this->isInstanceOf(RecipeWithExpensiveVersion::class),
                                ],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $mappedRecipe1,
                                $mappedRecipe2,
                            );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'mapNormalRecipes', $databaseRecipes, $recipeData);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapExpensiveRecipes(): void
    {
        $recipeData1 = new RecipeData();
        $recipeData1->setId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'));
        $recipeData2 = new RecipeData();
        $recipeData2->setId(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'));
        $recipeData3 = new RecipeData();
        $recipeData3->setId(Uuid::fromString('34c0f9b6-ec83-45d0-8283-cdfafd9d838a'));

        $recipeData = $this->createMock(RecipeDataCollection::class);
        $recipeData->expects($this->once())
                   ->method('getValues')
                   ->willReturn([$recipeData1, $recipeData2, $recipeData3]);

        $databaseRecipe1 = $this->createMock(DatabaseRecipe::class);
        $databaseRecipe2 = $this->createMock(DatabaseRecipe::class);
        $databaseRecipes = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => $databaseRecipe1,
            '34c0f9b6-ec83-45d0-8283-cdfafd9d838a' => $databaseRecipe2,
        ];

        $normalRecipe = new RecipeWithExpensiveVersion();
        $normalRecipe->name = 'abc';
        $normalRecipe->mode = 'def';
        $normalRecipes = ['abc' => $normalRecipe];

        $mappedRecipe1 = new RecipeWithExpensiveVersion();
        $mappedRecipe1->name = 'abc';
        $mappedRecipe2 = new RecipeWithExpensiveVersion();
        $mappedRecipe2->name = 'ghi';

        $expectedRecipe1 = new RecipeWithExpensiveVersion();
        $expectedRecipe1->name = 'abc';
        $expectedRecipe1->mode = 'def';
        $expectedRecipe1->expensiveVersion = $mappedRecipe1;
        $expectedResult = [
            'abc' => $expectedRecipe1,
            'ghi' => $mappedRecipe2,
        ];

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [
                                    $this->identicalTo($databaseRecipe1),
                                    $this->isInstanceOf(RecipeWithExpensiveVersion::class),
                                ],
                                [
                                    $this->identicalTo($databaseRecipe2),
                                    $this->isInstanceOf(RecipeWithExpensiveVersion::class),
                                ],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $mappedRecipe1,
                                $mappedRecipe2,
                            );

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'mapExpensiveRecipes', $databaseRecipes, $normalRecipes, $recipeData);

        $this->assertEquals($expectedResult, $result);
    }
}
