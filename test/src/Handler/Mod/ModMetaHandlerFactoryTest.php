<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Mod;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModMetaHandler;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModMetaHandlerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModMetaHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Mod\ModMetaHandlerFactory
 */
class ModMetaHandlerFactoryTest extends TestCase
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
        $container->expects($this->once())
                  ->method('get')
                  ->with(ModService::class)
                  ->willReturn($this->createMock(ModService::class));

        $factory = new ModMetaHandlerFactory();
        $factory($container, ModMetaHandler::class);
    }
}
