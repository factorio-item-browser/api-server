<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemRandomHandler;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemRandomHandlerFactory;
use FactorioItemBrowser\Api\Server\Mapper\ItemMapper;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ItemRandomHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\ItemRandomHandlerFactory
 */
class ItemRandomHandlerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(5))
                  ->method('get')
                  ->withConsecutive(
                      [ItemMapper::class],
                      [ItemService::class],
                      [RecipeMapper::class],
                      [RecipeService::class],
                      [TranslationService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(ItemMapper::class),
                      $this->createMock(ItemService::class),
                      $this->createMock(RecipeMapper::class),
                      $this->createMock(RecipeService::class),
                      $this->createMock(TranslationService::class)
                  );

        $factory = new ItemRandomHandlerFactory();
        $result = $factory($container, ItemRandomHandler::class);
        $this->assertInstanceOf(ItemRandomHandler::class, $result);
    }
}
