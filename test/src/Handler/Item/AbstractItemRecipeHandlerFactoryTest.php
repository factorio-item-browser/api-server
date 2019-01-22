<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Item\AbstractItemRecipeHandlerFactory;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemIngredientHandler;
use FactorioItemBrowser\Api\Server\Handler\Item\ItemProductHandler;
use FactorioItemBrowser\Api\Server\Mapper\ItemMapper;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AbstractItemRecipeHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Item\AbstractItemRecipeHandlerFactory
 */
class AbstractItemRecipeHandlerFactoryTest extends TestCase
{
    /**
     * Provides the data for the invoke test.
     * @return array
     */
    public function provideInvoke(): array
    {
        return [
            [ItemIngredientHandler::class],
            [ItemProductHandler::class],
        ];
    }

    /**
     * Tests the invoking.
     * @param string $className
     * @covers ::__invoke
     * @dataProvider provideInvoke
     */
    public function testInvoke(string $className): void
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

        $factory = new AbstractItemRecipeHandlerFactory();
        $factory($container, $className);
    }
}
