<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Service\RecipeService
 */
class RecipeServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var RecipeRepository&MockObject */
    private RecipeRepository $recipeRepository;

    protected function setUp(): void
    {
        $this->recipeRepository = $this->createMock(RecipeRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RecipeService&MockObject
     */
    private function createInstance(array $mockedMethods = []): RecipeService
    {
        return $this->getMockBuilder(RecipeService::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->recipeRepository,
                    ])
                    ->getMock();
    }

    public function testGetDataWithNames(): void
    {
        $names = ['abc', 'def'];

        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];

        $combinationId = $this->createMock(UuidInterface::class);
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByNames')
                               ->with($this->identicalTo($combinationId), $this->identicalTo($names))
                               ->willReturn($recipeData);

        $instance = $this->createInstance(['createDataCollection']);
        $instance->expects($this->once())
                 ->method('createDataCollection')
                 ->with($this->identicalTo($recipeData))
                 ->willReturn($recipeDataCollection);

        $result = $instance->getDataWithNames($combinationId, $names);

        $this->assertSame($recipeDataCollection, $result);
    }

    public function testGetAllData(): void
    {
        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];

        $combinationId = $this->createMock(UuidInterface::class);
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        $this->recipeRepository->expects($this->once())
                               ->method('findAllData')
                               ->with($this->identicalTo($combinationId))
                               ->willReturn($recipeData);

        $instance = $this->createInstance(['createDataCollection']);
        $instance->expects($this->once())
                 ->method('createDataCollection')
                 ->with($this->identicalTo($recipeData))
                 ->willReturn($recipeDataCollection);

        $result = $instance->getAllData($combinationId);

        $this->assertSame($recipeDataCollection, $result);
    }

    public function testGetDataWithIngredients(): void
    {
        $itemIds = [21, 7331];

        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];
        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];

        $combinationId = $this->createMock(UuidInterface::class);
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByIngredientItemIds')
                               ->with($this->identicalTo($combinationId), $this->identicalTo($itemIds))
                               ->willReturn($recipeData);

        $instance = $this->createInstance(['extractIdsFromItems', 'createDataCollection']);
        $instance->expects($this->once())
                 ->method('extractIdsFromItems')
                 ->with($this->identicalTo($items))
                 ->willReturn($itemIds);
        $instance->expects($this->once())
                 ->method('createDataCollection')
                 ->with($this->identicalTo($recipeData))
                 ->willReturn($recipeDataCollection);

        $result = $instance->getDataWithIngredients($combinationId, $items);

        $this->assertSame($recipeDataCollection, $result);
    }

    public function testGetDataWithProducts(): void
    {
        $itemIds = [21, 7331];

        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];
        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];

        $combinationId = $this->createMock(UuidInterface::class);
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByProductItemIds')
                               ->with($this->identicalTo($combinationId), $this->identicalTo($itemIds))
                               ->willReturn($recipeData);

        $instance = $this->createInstance(['extractIdsFromItems', 'createDataCollection']);
        $instance->expects($this->once())
                 ->method('extractIdsFromItems')
                 ->with($this->identicalTo($items))
                 ->willReturn($itemIds);
        $instance->expects($this->once())
                 ->method('createDataCollection')
                 ->with($this->identicalTo($recipeData))
                 ->willReturn($recipeDataCollection);

        $result = $instance->getDataWithProducts($combinationId, $items);

        $this->assertSame($recipeDataCollection, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testExtractIdsFromItems(): void
    {
        $id1 = $this->createMock(UuidInterface::class);
        $id2 = $this->createMock(UuidInterface::class);

        $item1 = new Item();
        $item1->setId($id1);

        $item2 = new Item();
        $item2->setId($id2);

        $items = [$item1, $item2];
        $expectedResult = [$id1, $id2];

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'extractIdsFromItems', $items);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateDataCollection(): void
    {
        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];

        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData2 = $this->createMock(RecipeData::class);

        $expectedResult = new RecipeDataCollection();
        $expectedResult->add($recipeData1)
                       ->add($recipeData2);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'createDataCollection', $recipeData);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetDetailsByIds(): void
    {
        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');
        $id3 = Uuid::fromString('56ebd60d-1852-45a5-abf5-2b31bbcb148d');
        $id4 = Uuid::fromString('28774d29-02cc-42d5-9c18-789bbbb64c0a');

        $ids = [$id1, $id2, $id3];

        $recipe1 = $this->createMock(Recipe::class);
        $recipe2 = $this->createMock(Recipe::class);
        $recipe3 = $this->createMock(Recipe::class);

        $recipeCache = [
            $id4->toString() => $recipe1,
            $id1->toString() => $recipe2,
            $id2->toString() => $recipe3,
        ];
        $expectedResult = [
            $id1->toString() => $recipe2,
            $id2->toString() => $recipe3,
        ];

        $instance = $this->createInstance(['fetchRecipeDetails']);
        $instance->expects($this->once())
                 ->method('fetchRecipeDetails')
                 ->with($this->identicalTo($ids));
        $this->injectProperty($instance, 'recipeCache', $recipeCache);

        $result = $instance->getDetailsByIds($ids);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchRecipeDetails(): void
    {
        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');
        $id3 = Uuid::fromString('56ebd60d-1852-45a5-abf5-2b31bbcb148d');

        $ids = [$id1, $id2, $id3];
        $expectedIds = [$id2, $id3];

        $recipe1 = new Recipe();
        $recipe1->setId($id1);
        $recipe2 = new Recipe();
        $recipe2->setId($id2);
        $recipe3 = new Recipe();
        $recipe3->setId($id3);

        $recipes = [$recipe2, $recipe3];
        $recipeCache = [
            $id1->toString() => $recipe1,
        ];
        $expectedRecipeCache = [
            $id1->toString() => $recipe1,
            $id2->toString() => $recipe2,
            $id3->toString() => $recipe3,
        ];

        $this->recipeRepository->expects($this->once())
                               ->method('findByIds')
                               ->with($this->equalTo($expectedIds))
                               ->willReturn($recipes);

        $instance = $this->createInstance();
        $this->injectProperty($instance, 'recipeCache', $recipeCache);

        $this->invokeMethod($instance, 'fetchRecipeDetails', $ids);

        $this->assertSame($expectedRecipeCache, $this->extractProperty($instance, 'recipeCache'));
    }
}
