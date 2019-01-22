<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemProductHandler;
use FactorioItemBrowser\Api\Server\Mapper\ItemMapper;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemProductHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\ItemProductHandler
 */
class ItemProductHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the fetchGroupedRecipeIds method.
     * @covers ::fetchGroupedRecipeIds
     */
    public function testFetchGroupedRecipeIds(): void
    {
        $item = new Item('abc', 'def');
        $item->setId(42);
        $items = [$item];

        /* @var RecipeService|MockObject $recipeService */
        $recipeService = $this->getMockBuilder(RecipeService::class)
                              ->setMethods(['getIdsWithProduct'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $recipeService->expects($this->once())
                      ->method('getIdsWithProduct')
                      ->with(42)
                      ->willReturn($items);

        /* @var ItemMapper $itemMapper */
        $itemMapper = $this->createMock(ItemMapper::class);
        /* @var ItemService $itemService */
        $itemService = $this->createMock(ItemService::class);
        /* @var RecipeMapper $recipeMapper */
        $recipeMapper = $this->createMock(RecipeMapper::class);
        /* @var TranslationService $translationService */
        $translationService = $this->createMock(TranslationService::class);

        $handler = new ItemProductHandler(
            $itemMapper,
            $itemService,
            $recipeMapper,
            $recipeService,
            $translationService
        );
        $result = $this->invokeMethod($handler, 'fetchGroupedRecipeIds', $item);
        $this->assertSame($items, $result);
    }
}
