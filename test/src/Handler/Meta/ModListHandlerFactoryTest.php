<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Meta;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModListHandler;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModListHandlerFactory;
use FactorioItemBrowser\Api\Server\Mapper\ModMapper;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModListHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Mod\ModListHandlerFactory
 */
class ModListHandlerFactoryTest extends TestCase
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
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [ModMapper::class],
                      [ModService::class],
                      [TranslationService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(ModMapper::class),
                      $this->createMock(ModService::class),
                      $this->createMock(TranslationService::class)
                  );

        $factory = new ModListHandlerFactory();
        $result = $factory($container, ModListHandler::class);
        $this->assertInstanceOf(ModListHandler::class, $result);
    }
}
