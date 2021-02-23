<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\SearchDecorator;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Search\Entity\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Api\Server\SearchDecorator\RecipeDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeDecorator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\SearchDecorator\RecipeDecorator
 */
class RecipeDecoratorTest extends TestCase
{
    use ReflectionTrait;

    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeService&MockObject */
    private RecipeService $recipeService;
    private int $numberOfRecipesPerResult = 42;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeService = $this->createMock(RecipeService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeDecorator&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeDecorator
    {
        $instance = $this->getMockBuilder(RecipeDecorator::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->mapperManager,
                             $this->recipeService,
                         ])
                         ->getMock();
        $instance->initialize($this->numberOfRecipesPerResult);
        return $instance;
    }

    public function testGetSupportedResultClass(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(RecipeResult::class, $instance->getSupportedResultClass());
    }

    public function testAnnounce(): void
    {
        $normalId = $this->createMock(UuidInterface::class);
        $expensiveId = $this->createMock(UuidInterface::class);

        $searchResult = new RecipeResult();
        $searchResult->setNormalRecipeId($normalId)
                     ->setExpensiveRecipeId($expensiveId);


        $instance = $this->createInstance(['addAnnouncedId']);
        $instance->expects($this->exactly(2))
                 ->method('addAnnouncedId')
                 ->withConsecutive(
                     [$this->identicalTo($normalId)],
                     [$this->identicalTo($expensiveId)],
                 );

        $instance->announce($searchResult);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchDatabaseEntities(): void
    {
        $ids = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];
        $databaseRecipes = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => $this->createMock(DatabaseRecipe::class),
            '2a098339-f069-4941-a0f8-1f9518dc036b' => $this->createMock(DatabaseRecipe::class),
        ];

        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo($ids))
                            ->willReturn($databaseRecipes);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchDatabaseEntities', $ids);

        $this->assertSame($databaseRecipes, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideGetIdFromResult(): array
    {
        $normalId = $this->createMock(UuidInterface::class);
        $expensiveId = $this->createMock(UuidInterface::class);

        $searchResult1 = new RecipeResult();
        $searchResult1->setNormalRecipeId($normalId)
                      ->setExpensiveRecipeId($expensiveId);

        $searchResult2 = new RecipeResult();
        $searchResult2->setNormalRecipeId($normalId);

        $searchResult3 = new RecipeResult();
        $searchResult3->setExpensiveRecipeId($expensiveId);

        $searchResult4 = new RecipeResult();

        return [
            [$searchResult1, $normalId],
            [$searchResult2, $normalId],
            [$searchResult3, $expensiveId],
            [$searchResult4, null],
        ];
    }

    /**
     * @param RecipeResult $searchResult
     * @param UuidInterface|null $expectedResult
     * @throws ReflectionException
     * @dataProvider provideGetIdFromResult
     */
    public function testGetIdFromResult(RecipeResult $searchResult, ?UuidInterface $expectedResult): void
    {
        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getIdFromResult', $searchResult);

        $this->assertSame($expectedResult, $result);
    }


    /**
     * @return array<mixed>
     */
    public function provideDecorate(): array
    {
        $recipe1 = new RecipeResult();
        $recipe1->setNormalRecipeId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'))
                ->setExpensiveRecipeId(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'));

        $recipe2 = new RecipeResult();
        $recipe2->setExpensiveRecipeId(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'));

        $recipe3 = new RecipeResult();

        return [
            [$recipe1, Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b')],
            [$recipe2, Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b')],
            [$recipe3, null],
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testHydrateRecipes(): void
    {
        $recipe = $this->createMock(RecipeWithExpensiveVersion::class);
        $searchResult = $this->createMock(RecipeResult::class);

        $entity = new GenericEntityWithRecipes();
        $entity->name = 'abc';

        $expectedEntity = new GenericEntityWithRecipes();
        $expectedEntity->name = 'abc';
        $expectedEntity->recipes = [$recipe];
        $expectedEntity->totalNumberOfRecipes = 1;

        $instance = $this->createInstance(['decorateRecipe']);
        $instance->expects($this->once())
                 ->method('decorateRecipe')
                 ->with($searchResult)
                 ->willReturn($recipe);

        $this->invokeMethod($instance, 'hydrateRecipes', $searchResult, $entity);

        $this->assertEquals($expectedEntity, $entity);
    }

    /**
     * @throws ReflectionException
     */
    public function testHydrateRecipesWithoutRecipe(): void
    {
        $searchResult = $this->createMock(RecipeResult::class);

        $entity = new GenericEntityWithRecipes();
        $entity->name = 'abc';

        $expectedEntity = new GenericEntityWithRecipes();
        $expectedEntity->name = 'abc';

        $instance = $this->createInstance(['decorateRecipe']);
        $instance->expects($this->once())
                 ->method('decorateRecipe')
                 ->with($searchResult)
                 ->willReturn(null);

        $this->invokeMethod($instance, 'hydrateRecipes', $searchResult, $entity);

        $this->assertEquals($expectedEntity, $entity);
    }

    /**
     * @return array<mixed>
     */
    public function provideDecorateRecipe(): array
    {
        $recipe1 = new RecipeWithExpensiveVersion();
        $recipe1->name = 'abc';
        $recipe2 = new RecipeWithExpensiveVersion();
        $recipe2->name = 'def';

        $combinedRecipe = new RecipeWithExpensiveVersion();
        $combinedRecipe->name = 'abc';
        $combinedRecipe->expensiveVersion = $recipe2;

        return [
            [$recipe1, $recipe2, $combinedRecipe],
            [$recipe1, null, $recipe1],
            [null, $recipe2, $recipe2],
        ];
    }

    /**
     * @param RecipeWithExpensiveVersion|null $normalRecipe
     * @param RecipeWithExpensiveVersion|null $expensiveRecipe
     * @param RecipeWithExpensiveVersion|null $expectedResult
     * @dataProvider provideDecorateRecipe
     */
    public function testDecorateRecipe(
        ?RecipeWithExpensiveVersion $normalRecipe,
        ?RecipeWithExpensiveVersion $expensiveRecipe,
        ?RecipeWithExpensiveVersion $expectedResult
    ): void {
        $recipeResult = new RecipeResult();
        $recipeResult->setNormalRecipeId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'))
                     ->setExpensiveRecipeId(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'));

        $instance = $this->createInstance(['mapEntityWithId']);
        $instance->expects($this->exactly(2))
                 ->method('mapEntityWithId')
                 ->withConsecutive(
                     [
                         $this->equalTo(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b')),
                         $this->isInstanceOf(RecipeWithExpensiveVersion::class),
                     ],
                     [
                         $this->equalTo(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b')),
                         $this->isInstanceOf(RecipeWithExpensiveVersion::class),
                     ],
                 )
                 ->willReturnOnConsecutiveCalls(
                     $normalRecipe,
                     $expensiveRecipe,
                 );

        $result = $instance->decorateRecipe($recipeResult);

        $this->assertEquals($expectedResult, $result);
    }
}
