<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Recipe;

use FactorioItemBrowser\Api\Server\Database\Service\MachineService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeMachinesHandler;
use FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeMachinesHandlerFactory;
use FactorioItemBrowser\Api\Server\Mapper\MachineMapper;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RecipeMachinesHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Recipe\RecipeMachinesHandlerFactory
 */
class RecipeMachinesHandlerFactoryTest extends TestCase
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
        $container->expects($this->exactly(4))
                  ->method('get')
                  ->withConsecutive(
                      [MachineMapper::class],
                      [MachineService::class],
                      [RecipeService::class],
                      [TranslationService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(MachineMapper::class),
                      $this->createMock(MachineService::class),
                      $this->createMock(RecipeService::class),
                      $this->createMock(TranslationService::class)
                  );

        $factory = new RecipeMachinesHandlerFactory();
        $factory($container, RecipeMachinesHandler::class);
    }
}
