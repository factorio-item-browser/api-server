<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Handler\Item\AbstractItemRecipeHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractItemRecipeHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\AbstractItemRecipeHandler
 */
class AbstractItemRecipeHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked item repository.
     * @var ItemRepository&MockObject
     */
    protected $itemRepository;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * The mocked recipe service.
     * @var RecipeService&MockObject
     */
    protected $recipeService;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->itemRepository = $this->createMock(ItemRepository::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->recipeService = $this->createMock(RecipeService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();

        $this->assertSame($this->itemRepository, $this->extractProperty($handler, 'itemRepository'));
        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
        $this->assertSame($this->recipeService, $this->extractProperty($handler, 'recipeService'));
    }

    /**
     * Tests the fetchItem method.
     * @throws ReflectionException
     * @covers ::fetchItem
     */
    public function testFetchItem(): void
    {
        $type = 'abc';
        $name = 'def';
        $enabledModCombinationIds = [42, 1337];

        $expectedNamesByTypes = [
            'abc' => ['def'],
        ];

        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        $items = [$item];

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);

        $this->itemRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with(
                                 $this->equalTo($expectedNamesByTypes),
                                 $this->identicalTo($enabledModCombinationIds)
                             )
                             ->willReturn($items);

        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($handler, 'fetchItem', $type, $name, $authorizationToken);

        $this->assertSame($item, $result);
    }

    /**
     * Tests the fetchItem method.
     * @throws ReflectionException
     * @covers ::fetchItem
     */
    public function testFetchItemWithException(): void
    {
        $type = 'abc';
        $name = 'def';
        $enabledModCombinationIds = [42, 1337];

        $expectedNamesByTypes = [
            'abc' => ['def'],
        ];

        $items = [];

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);

        $this->itemRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with(
                                 $this->equalTo($expectedNamesByTypes),
                                 $this->identicalTo($enabledModCombinationIds)
                             )
                             ->willReturn($items);

        $this->expectException(EntityNotFoundException::class);

        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();

        $this->invokeMethod($handler, 'fetchItem', $type, $name, $authorizationToken);
    }

    /**
     * Tests the createResponseEntity method.
     * @throws ReflectionException
     * @covers ::createResponseEntity
     */
    public function testCreateResponseEntity(): void
    {
        $totalNumberOfRecipes = 42;

        /* @var Item&MockObject $item */
        $item = $this->createMock(Item::class);
        /* @var RecipeDataCollection&MockObject $recipeData */
        $recipeData = $this->createMock(RecipeDataCollection::class);

        $expectedResult = new GenericEntityWithRecipes();
        $expectedResult->setTotalNumberOfRecipes($totalNumberOfRecipes);

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($item), $this->isInstanceOf(GenericEntityWithRecipes::class)],
                                [$this->identicalTo($recipeData), $this->isInstanceOf(GenericEntityWithRecipes::class)]
                            );

        /* @var AbstractItemRecipeHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractItemRecipeHandler::class)
                        ->setConstructorArgs([$this->itemRepository, $this->mapperManager, $this->recipeService])
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($handler, 'createResponseEntity', $item, $recipeData, $totalNumberOfRecipes);

        $this->assertEquals($expectedResult, $result);
    }
}
