<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\SearchDecorator;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Search\Entity\Result\ItemResult;
use FactorioItemBrowser\Api\Search\Entity\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\SearchDecorator\ItemDecorator;
use FactorioItemBrowser\Api\Server\SearchDecorator\RecipeDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the ItemDecorator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\SearchDecorator\ItemDecorator
 */
class ItemDecoratorTest extends TestCase
{
    use ReflectionTrait;

    /** @var ItemRepository&MockObject */
    private ItemRepository $itemRepository;
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var RecipeDecorator&MockObject */
    private RecipeDecorator $recipeDecorator;
    private int $numberOfRecipesPerResult = 42;

    protected function setUp(): void
    {
        $this->itemRepository = $this->createMock(ItemRepository::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeDecorator = $this->createMock(RecipeDecorator::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ItemDecorator&MockObject
     */
    private function createInstance(array $mockedMethods = []): ItemDecorator
    {
        $instance = $this->getMockBuilder(ItemDecorator::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->itemRepository,
                             $this->mapperManager,
                             $this->recipeDecorator,
                         ])
                         ->getMock();
        $instance->initialize($this->numberOfRecipesPerResult);
        return $instance;
    }

    public function testGetSupportedResultClass(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(ItemResult::class, $instance->getSupportedResultClass());
    }

    public function testAnnounce(): void
    {
        $id = Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1');
        $recipe1 = $this->createMock(RecipeResult::class);
        $recipe2 = $this->createMock(RecipeResult::class);
        $recipe3 = $this->createMock(RecipeResult::class);

        $searchResult = new ItemResult();
        $searchResult->setId($id)
                     ->addRecipe($recipe1)
                     ->addRecipe($recipe2)
                     ->addRecipe($recipe3);

        $this->numberOfRecipesPerResult = 2;
        $this->recipeDecorator->expects($this->exactly(2))
                              ->method('announce')
                              ->withConsecutive(
                                  [$this->identicalTo($recipe1)],
                                  [$this->identicalTo($recipe2)]
                              );

        $instance = $this->createInstance(['addAnnouncedId']);
        $instance->expects($this->once())
                 ->method('addAnnouncedId')
                 ->with($this->identicalTo($id));

        $instance->announce($searchResult);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchDatabaseEntities(): void
    {
        $ids = [
            Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1'),
            Uuid::fromString('24db0d5a-a933-4e46-bb5a-0b7d88c6272e'),
        ];

        $item1 = new DatabaseItem();
        $item1->setId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'));
        $item2 = new DatabaseItem();
        $item2->setId(Uuid::fromString('2a098339-f069-4941-a0f8-1f9518dc036b'));

        $expectedResult = [
            '16cbc2ac-266d-4249-beb1-8c07850b732b' => $item1,
            '2a098339-f069-4941-a0f8-1f9518dc036b' => $item2,
        ];

        $this->itemRepository->expects($this->once())
                             ->method('findByIds')
                             ->with($this->identicalTo($ids))
                             ->willReturn([$item1, $item2]);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchDatabaseEntities', $ids);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetIdFromResult(): void
    {
        $id = Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1');

        $searchResult = new ItemResult();
        $searchResult->setId($id);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'getIdFromResult', $searchResult);

        $this->assertSame($id, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testHydrateRecipes(): void
    {
        $recipeResult1 = $this->createMock(RecipeResult::class);
        $recipeResult2 = $this->createMock(RecipeResult::class);
        $recipeResult3 = $this->createMock(RecipeResult::class);
        $recipeResult4 = $this->createMock(RecipeResult::class);
        $recipe1 = $this->createMock(RecipeWithExpensiveVersion::class);
        $recipe2 = $this->createMock(RecipeWithExpensiveVersion::class);

        $searchResult = new ItemResult();
        $searchResult->setId(Uuid::fromString('16cbc2ac-266d-4249-beb1-8c07850b732b'))
                     ->addRecipe($recipeResult1)
                     ->addRecipe($recipeResult2)
                     ->addRecipe($recipeResult3)
                     ->addRecipe($recipeResult4);

        $entity = new GenericEntityWithRecipes();
        $entity->name = 'abc';

        $expectedEntity = new GenericEntityWithRecipes();
        $expectedEntity->name = 'abc';
        $expectedEntity->recipes = [$recipe1, $recipe2];
        $expectedEntity->totalNumberOfRecipes = 4;

        $this->numberOfRecipesPerResult = 3;
        $this->recipeDecorator->expects($this->exactly(3))
                              ->method('decorateRecipe')
                              ->withConsecutive(
                                  [$this->identicalTo($recipeResult1)],
                                  [$this->identicalTo($recipeResult2)],
                                  [$this->identicalTo($recipeResult3)],
                              )
                              ->willReturnOnConsecutiveCalls(
                                  $recipe1,
                                  null,
                                  $recipe2
                              );

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'hydrateRecipes', $searchResult, $entity);

        $this->assertEquals($expectedEntity, $entity);
    }
}
