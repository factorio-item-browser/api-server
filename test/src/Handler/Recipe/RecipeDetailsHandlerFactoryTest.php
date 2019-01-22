<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Recipe;

use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeDetailsHandler;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeDetailsHandlerFactory;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeDetailsHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeDetailsHandlerFactory
 */
class RecipeDetailsHandlerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [RecipeMapper::class],
                      [RecipeService::class],
                      [TranslationService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(RecipeMapper::class),
                      $this->createMock(RecipeService::class),
                      $this->createMock(TranslationService::class)
                  );

        $factory = new RecipeDetailsHandlerFactory();
        $factory($container, RecipeDetailsHandler::class);
    }
}
