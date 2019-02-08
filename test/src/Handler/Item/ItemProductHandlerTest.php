<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use BluePsyduck\Common\Test\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemProductHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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
     * @throws ReflectionException
     */
    public function testFetchGroupedRecipeIds(): void
    {
        $item = new Item('abc', 'def');
        $item->setId(42);
        $items = [$item];

        /* @var RecipeService&MockObject $recipeService */
        $recipeService = $this->createMock(RecipeService::class);
        $recipeService->expects($this->once())
                      ->method('getIdsWithProduct')
                      ->with(42)
                      ->willReturn($items);

        /* @var ItemService&MockObject $itemService */
        $itemService = $this->createMock(ItemService::class);
        /* @var MapperManagerInterface&MockObject $mapperManager */
        $mapperManager = $this->createMock(MapperManagerInterface::class);
        /* @var TranslationService&MockObject $translationService */
        $translationService = $this->createMock(TranslationService::class);

        $handler = new ItemProductHandler(
            $itemService,
            $mapperManager,
            $recipeService,
            $translationService
        );
        $result = $this->invokeMethod($handler, 'fetchGroupedRecipeIds', $item);

        $this->assertSame($items, $result);
    }
}
