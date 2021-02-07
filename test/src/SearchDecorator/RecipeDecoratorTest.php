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
        return $this->getMockBuilder(RecipeDecorator::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->mapperManager,
                        $this->recipeService,
                    ])
                    ->getMock();
    }

    public function testGetSupportedResultClass(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(RecipeResult::class, $instance->getSupportedResultClass());
    }

    /**
     * @throws ReflectionException
     */
    public function testInitialize(): void
    {
        $instance = $this->createInstance();
        $this->injectProperty($instance, 'announcedRecipeIds', [$this->createMock(UuidInterface::class)]);
        $this->injectProperty($instance, 'databaseRecipes', [$this->createMock(RecipeResult::class)]);

        $instance->initialize(42);

        $this->assertSame([], $this->extractProperty($instance, 'announcedRecipeIds'));
        $this->assertSame([], $this->extractProperty($instance, 'databaseRecipes'));
    }

    /**
     * @throws ReflectionException
     */
    public function testAnnounce(): void
    {
        $announcedRecipeIds = [
            '04041eb4-cb94-4c47-8b17-90a5f5b28cae' => Uuid::fromString('04041eb4-cb94-4c47-8b17-90a5f5b28cae'),
        ];
        $expectedAnnouncedRecipeIds = [
            '04041eb4-cb94-4c47-8b17-90a5f5b28cae' => Uuid::fromString('04041eb4-cb94-4c47-8b17-90a5f5b28cae'),
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'),
            '2a098339-f069-4941-a0f8-1f9518dc036b' => Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'),
        ];

        $recipeResult = new RecipeResult();
        $recipeResult->setNormalRecipeId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'))
                     ->setExpensiveRecipeId(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'));

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'announcedRecipeIds', $announcedRecipeIds);

        $instance->announce($recipeResult);

        $this->assertEquals($expectedAnnouncedRecipeIds, $this->extractProperty($instance, 'announcedRecipeIds'));
    }

    /**
     * @throws ReflectionException
     */
    public function testPrepare(): void
    {
        $announcedRecipeIds = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'),
            '2a098339-f069-4941-a0f8-1f9518dc036b' => Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'),
        ];
        $databaseRecipes = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => $this->createMock(DatabaseRecipe::class),
            '2a098339-f069-4941-a0f8-1f9518dc036b' => $this->createMock(DatabaseRecipe::class),
        ];

        $this->recipeService->expects($this->once())
                            ->method('getDetailsByIds')
                            ->with($this->identicalTo($announcedRecipeIds))
                            ->willReturn($databaseRecipes);

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'announcedRecipeIds', $announcedRecipeIds);

        $instance->prepare();

        $this->assertSame($databaseRecipes, $this->extractProperty($instance, 'databaseRecipes'));
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
     * @param RecipeResult $searchResult
     * @param UuidInterface|null $expectedId
     * @dataProvider provideDecorate
     */
    public function testDecorate(RecipeResult $searchResult, ?UuidInterface $expectedId): void
    {
        $decoratedRecipe = $this->createMock(RecipeWithExpensiveVersion::class);

        $entity = new GenericEntityWithRecipes();
        $entity->name = 'abc';

        $expectedResult = new GenericEntityWithRecipes();
        $expectedResult->name = 'abc';
        $expectedResult->recipes = [$decoratedRecipe];
        $expectedResult->totalNumberOfRecipes = 1;

        $instance = $this->createInstance(['decorateRecipe', 'mapRecipeWithId']);
        $instance->expects($this->once())
                 ->method('mapRecipeWithId')
                 ->with($this->equalTo($expectedId), $this->isInstanceOf(GenericEntityWithRecipes::class))
                 ->willReturn($entity);
        $instance->expects($this->once())
                 ->method('decorateRecipe')
                 ->with($this->identicalTo($searchResult))
                 ->willReturn($decoratedRecipe);


        $result = $instance->decorate($searchResult);

        $this->assertEquals($expectedResult, $result);
    }

    public function testDecorateWithoutDecoratedRecipe(): void
    {
        $searchResult = new RecipeResult();
        $searchResult->setNormalRecipeId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'));
        $expectedId = Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b');

        $entity = new GenericEntityWithRecipes();
        $entity->name = 'abc';

        $expectedResult = new GenericEntityWithRecipes();
        $expectedResult->name = 'abc';

        $instance = $this->createInstance(['decorateRecipe', 'mapRecipeWithId']);
        $instance->expects($this->once())
                 ->method('mapRecipeWithId')
                 ->with($this->equalTo($expectedId), $this->isInstanceOf(GenericEntityWithRecipes::class))
                 ->willReturn($entity);
        $instance->expects($this->once())
                 ->method('decorateRecipe')
                 ->with($this->identicalTo($searchResult))
                 ->willReturn(null);

        $result = $instance->decorate($searchResult);

        $this->assertEquals($expectedResult, $result);
    }

    public function testDecorateWithoutEntity(): void
    {
        $searchResult = new RecipeResult();
        $searchResult->setNormalRecipeId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'));
        $expectedId = Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b');

        $instance = $this->createInstance(['decorateRecipe', 'mapRecipeWithId']);
        $instance->expects($this->once())
                 ->method('mapRecipeWithId')
                 ->with($this->equalTo($expectedId), $this->isInstanceOf(GenericEntityWithRecipes::class))
                 ->willReturn(null);
        $instance->expects($this->never())
                 ->method('decorateRecipe');

        $result = $instance->decorate($searchResult);

        $this->assertNull($result);
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

        $instance = $this->createInstance(['mapRecipeWithId']);
        $instance->expects($this->exactly(2))
                 ->method('mapRecipeWithId')
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

    /**
     * @throws ReflectionException
     */
    public function testMapRecipeWithId(): void
    {
        $recipeId = Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b');
        $databaseRecipe = $this->createMock(DatabaseRecipe::class);
        $destination = $this->createMock(GenericEntityWithRecipes::class);
        $mappedRecipe = $this->createMock(GenericEntityWithRecipes::class);

        $databaseRecipes = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => $databaseRecipe,
        ];

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with(
                                $this->identicalTo($databaseRecipe),
                                $this->identicalTo($destination),
                            )
                            ->willReturn($mappedRecipe);

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'databaseRecipes', $databaseRecipes);

        $result = $this->invokeMethod($instance, 'mapRecipeWithId', $recipeId, $destination);

        $this->assertSame($mappedRecipe, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapRecipeWithIdWithoutDatabaseRecipe(): void
    {
        $recipeId = Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b');
        $destination = $this->createMock(GenericEntityWithRecipes::class);

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'mapRecipeWithId', $recipeId, $destination);

        $this->assertNull($result);
    }

    /**
     * @throws ReflectionException
     */
    public function testMapRecipeWithIdWithoutId(): void
    {
        $destination = $this->createMock(GenericEntityWithRecipes::class);

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'mapRecipeWithId', null, $destination);

        $this->assertNull($result);
    }
}
