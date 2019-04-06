<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Filter\DataFilter;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
     * The mocked data filter.
     * @var DataFilter&MockObject
     */
    protected $dataFilter;

    /**
     * The mocked recipe repository.
     * @var RecipeRepository&MockObject
     */
    protected $recipeRepository;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dataFilter = $this->createMock(DataFilter::class);
        $this->recipeRepository = $this->createMock(RecipeRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new RecipeService($this->dataFilter, $this->recipeRepository);

        $this->assertSame($this->dataFilter, $this->extractProperty($service, 'dataFilter'));
        $this->assertSame($this->recipeRepository, $this->extractProperty($service, 'recipeRepository'));
        $this->assertSame([], $this->extractProperty($service, 'recipeCache'));
    }

    /**
     * Tests the getDataWithNames method.
     * @throws ReflectionException
     * @covers ::getDataWithNames
     */
    public function testGetDataWithNames(): void
    {
        $names = ['abc', 'def'];
        $enabledModCombinationIds = [42, 1337];

        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];
        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByNames')
                               ->with($this->identicalTo($names), $this->identicalTo($enabledModCombinationIds))
                               ->willReturn($recipeData);

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->setMethods(['createDataCollection'])
                        ->setConstructorArgs([$this->dataFilter, $this->recipeRepository])
                        ->getMock();
        $service->expects($this->once())
                ->method('createDataCollection')
                ->with($this->identicalTo($recipeData))
                ->willReturn($recipeDataCollection);

        $result = $service->getDataWithNames($names, $authorizationToken);

        $this->assertSame($recipeDataCollection, $result);
    }

    /**
     * Tests the getDataWithIngredients method.
     * @throws ReflectionException
     * @covers ::getDataWithIngredients
     */
    public function testGetDataWithIngredients(): void
    {
        $enabledModCombinationIds = [42, 1337];
        $itemIds = [21, 7331];

        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];
        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];
        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByIngredientItemIds')
                               ->with($this->identicalTo($itemIds), $this->identicalTo($enabledModCombinationIds))
                               ->willReturn($recipeData);

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->setMethods(['extractIdsFromItems', 'createDataCollection'])
                        ->setConstructorArgs([$this->dataFilter, $this->recipeRepository])
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
     * @throws ReflectionException
     * @covers ::getDataWithProducts
     */
    public function testGetDataWithProducts(): void
    {
        $enabledModCombinationIds = [42, 1337];
        $itemIds = [21, 7331];

        $items = [
            $this->createMock(Item::class),
            $this->createMock(Item::class),
        ];
        $recipeData = [
            $this->createMock(RecipeData::class),
            $this->createMock(RecipeData::class),
        ];
        /* @var RecipeDataCollection&MockObject $recipeDataCollection */
        $recipeDataCollection = $this->createMock(RecipeDataCollection::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);

        $this->recipeRepository->expects($this->once())
                               ->method('findDataByProductItemIds')
                               ->with($this->identicalTo($itemIds), $this->identicalTo($enabledModCombinationIds))
                               ->willReturn($recipeData);

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->setMethods(['extractIdsFromItems', 'createDataCollection'])
                        ->setConstructorArgs([$this->dataFilter, $this->recipeRepository])
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
        /* @var Item&MockObject $item1 */
        $item1 = $this->createMock(Item::class);
        $item1->expects($this->once())
              ->method('getId')
              ->willReturn(42);

        /* @var Item&MockObject $item2 */
        $item2 = $this->createMock(Item::class);
        $item2->expects($this->once())
              ->method('getId')
              ->willReturn(1337);

        $items = [$item1, $item2];
        $expectedResult = [42, 1337];

        $service = new RecipeService($this->dataFilter, $this->recipeRepository);
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

        $filteredRecipeData = [
            $recipeData1,
            $recipeData2
        ];

        $expectedResult = new RecipeDataCollection();
        $expectedResult->add($recipeData1)
                       ->add($recipeData2);

        $this->dataFilter->expects($this->once())
                         ->method('filter')
                         ->with($this->identicalTo($recipeData))
                         ->willReturn($filteredRecipeData);

        $service = new RecipeService($this->dataFilter, $this->recipeRepository);
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
        $ids = [42, 1337, 7331];

        /* @var Recipe&MockObject $recipe1 */
        $recipe1 = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $recipe2 */
        $recipe2 = $this->createMock(Recipe::class);
        /* @var Recipe&MockObject $recipe3 */
        $recipe3 = $this->createMock(Recipe::class);

        $recipeCache = [
            21 => $recipe1,
            42 => $recipe2,
            1337 => $recipe3,
        ];
        $expectedResult = [
            42 => $recipe2,
            1337 => $recipe3,
        ];

        /* @var RecipeService&MockObject $service */
        $service = $this->getMockBuilder(RecipeService::class)
                        ->setMethods(['fetchRecipeDetails'])
                        ->setConstructorArgs([$this->dataFilter, $this->recipeRepository])
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
        $ids = [21, 42, 1337];
        $expectedIds = [21, 1337];

        /* @var Recipe&MockObject $recipe1 */
        $recipe1 = $this->createMock(Recipe::class);

        /* @var Recipe&MockObject $recipe2 */
        $recipe2 = $this->createMock(Recipe::class);
        $recipe2->expects($this->once())
                ->method('getId')
                ->willReturn(21);

        /* @var Recipe&MockObject $recipe3 */
        $recipe3 = $this->createMock(Recipe::class);
        $recipe3->expects($this->once())
                ->method('getId')
                ->willReturn(1337);

        $recipes = [$recipe2, $recipe3];
        $recipeCache = [
            42 => $recipe1,
        ];
        $expectedRecipeCache = [
            42 => $recipe1,
            21 => $recipe2,
            1337 => $recipe3,
        ];

        $this->recipeRepository->expects($this->once())
                               ->method('findByIds')
                               ->with($this->equalTo($expectedIds))
                               ->willReturn($recipes);

        $service = new RecipeService($this->dataFilter, $this->recipeRepository);
        $this->injectProperty($service, 'recipeCache', $recipeCache);

        $this->invokeMethod($service, 'fetchRecipeDetails', $ids);

        $this->assertSame($expectedRecipeCache, $this->extractProperty($service, 'recipeCache'));
    }
}
