<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
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
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\RecipeService
 */
class RecipeServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked recipe repository.
     * @var RecipeRepository&MockObject
     */
    protected $recipeRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recipeRepository = $this->createMock(RecipeRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new RecipeService($this->recipeRepository);

        $this->assertSame($this->recipeRepository, $this->extractProperty($service, 'recipeRepository'));
        $this->assertSame([], $this->extractProperty($service, 'recipeCache'));
    }

    /**
     * Tests the getDataWithNames method.
     * @covers ::getDataWithNames
     */
    public function testGetDataWithNames(): void
    {
        $names = ['abc', 'def'];

        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByNames')
                               ->with($this->identicalTo($combinationId), $this->identicalTo($names))
                               ->willReturn($recipeData);

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->onlyMethods(['createDataCollection'])
                        ->setConstructorArgs([$this->recipeRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('createDataCollection')
                ->with($this->identicalTo($recipeData))
                ->willReturn($recipeDataCollection);

        $result = $service->getDataWithNames($names, $authorizationToken);

        $this->assertSame($recipeDataCollection, $result);
    }

    /**
     * Tests the getAllData method.
     * @covers ::getAllData
     */
    public function testGetAllData(): void
    {
        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->recipeRepository->expects($this->once())
                               ->method('findAllData')
                               ->with($this->identicalTo($combinationId))
                               ->willReturn($recipeData);

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->onlyMethods(['createDataCollection'])
                        ->setConstructorArgs([$this->recipeRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('createDataCollection')
                ->with($this->identicalTo($recipeData))
                ->willReturn($recipeDataCollection);

        $result = $service->getAllData($authorizationToken);

        $this->assertSame($recipeDataCollection, $result);
    }

    /**
     * Tests the getDataWithIngredients method.
     * @covers ::getDataWithIngredients
     */
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

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByIngredientItemIds')
                               ->with($this->identicalTo($combinationId), $this->identicalTo($itemIds))
                               ->willReturn($recipeData);

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->onlyMethods(['extractIdsFromItems', 'createDataCollection'])
                        ->setConstructorArgs([$this->recipeRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('extractIdsFromItems')
                ->with($this->identicalTo($items))
                ->willReturn($itemIds);
        $service->expects($this->once())
                ->method('createDataCollection')
                ->with($this->identicalTo($recipeData))
                ->willReturn($recipeDataCollection);

        $result = $service->getDataWithIngredients($items, $authorizationToken);

        $this->assertSame($recipeDataCollection, $result);
    }

    /**
     * Tests the getDataWithProducts method.
     * @covers ::getDataWithProducts
     */
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

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByProductItemIds')
                               ->with($this->identicalTo($combinationId), $this->identicalTo($itemIds))
                               ->willReturn($recipeData);

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->onlyMethods(['extractIdsFromItems', 'createDataCollection'])
                        ->setConstructorArgs([$this->recipeRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('extractIdsFromItems')
                ->with($this->identicalTo($items))
                ->willReturn($itemIds);
        $service->expects($this->once())
                ->method('createDataCollection')
                ->with($this->identicalTo($recipeData))
                ->willReturn($recipeDataCollection);

        $result = $service->getDataWithProducts($items, $authorizationToken);

        $this->assertSame($recipeDataCollection, $result);
    }

    /**
     * Tests the extractIdsFromItems method.
     * @throws ReflectionException
     * @covers ::extractIdsFromItems
     */
    public function testExtractIdsFromItems(): void
    {
        /* @var UuidInterface&MockObject $id1 */
        $id1 = $this->createMock(UuidInterface::class);
        /* @var UuidInterface&MockObject $id2 */
        $id2 = $this->createMock(UuidInterface::class);

        /* @var Item&MockObject $item1 */
        $item1 = $this->createMock(Item::class);
        $item1->expects($this->once())
              ->method('getId')
              ->willReturn($id1);

        /* @var Item&MockObject $item2 */
        $item2 = $this->createMock(Item::class);
        $item2->expects($this->once())
              ->method('getId')
              ->willReturn($id2);

        $items = [$item1, $item2];
        $expectedResult = [$id1, $id2];

        $service = new RecipeService($this->recipeRepository);
        $result = $this->invokeMethod($service, 'extractIdsFromItems', $items);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createDataCollection method.
     * @throws ReflectionException
     * @covers ::createDataCollection
     */
    public function testCreateDataCollection(): void
    {
        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];

        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);

        $expectedResult = new RecipeDataCollection();
        $expectedResult->add($recipeData1)
                       ->add($recipeData2);

        $service = new RecipeService($this->recipeRepository);
        $result = $this->invokeMethod($service, 'createDataCollection', $recipeData);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getDetailsByIds method.
     * @throws ReflectionException
     * @covers ::getDetailsByIds
     */
    public function testGetDetailsByIds(): void
    {
        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');
        $id3 = Uuid::fromString('56ebd60d-1852-45a5-abf5-2b31bbcb148d');
        $id4 = Uuid::fromString('28774d29-02cc-42d5-9c18-789bbbb64c0a');

        $ids = [$id1, $id2, $id3];

        /* @var Recipe&MockObject $recipe1 */
        $recipe1 = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $recipe2 */
        $recipe2 = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $recipe3 */
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

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->onlyMethods(['fetchRecipeDetails'])
                        ->setConstructorArgs([$this->recipeRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('fetchRecipeDetails')
                ->with($this->identicalTo($ids));
        $this->injectProperty($service, 'recipeCache', $recipeCache);

        $result = $service->getDetailsByIds($ids);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the fetchRecipeDetails method.
     * @throws ReflectionException
     * @covers ::fetchRecipeDetails
     */
    public function testFetchRecipeDetails(): void
    {
        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');
        $id3 = Uuid::fromString('56ebd60d-1852-45a5-abf5-2b31bbcb148d');

        $ids = [$id1, $id2, $id3];
        $expectedIds = [$id2, $id3];

        /* @var Recipe&MockObject $recipe1 */
        $recipe1 = $this->createMock(Recipe::class);
        $recipe1->expects($this->any())
                ->method('getId')
                ->willReturn($id1);

        /* @var Recipe&MockObject $recipe2 */
        $recipe2 = $this->createMock(Recipe::class);
        $recipe2->expects($this->any())
                ->method('getId')
                ->willReturn($id2);

        /* @var Recipe&MockObject $recipe3 */
        $recipe3 = $this->createMock(Recipe::class);
        $recipe3->expects($this->any())
                ->method('getId')
                ->willReturn($id3);

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

        $service = new RecipeService($this->recipeRepository);
        $this->injectProperty($service, 'recipeCache', $recipeCache);

        $this->invokeMethod($service, 'fetchRecipeDetails', $ids);

        $this->assertSame($expectedRecipeCache, $this->extractProperty($service, 'recipeCache'));
    }
}
