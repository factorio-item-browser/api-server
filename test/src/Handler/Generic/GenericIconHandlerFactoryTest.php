<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use FactorioItemBrowser\Api\Server\Database\Service\IconService;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericIconHandler;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericIconHandlerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the GenericIconHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Generic\GenericIconHandlerFactory
 */
class GenericIconHandlerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo(IconService::class))
                  ->willReturn($this->createMock(IconService::class));

        $factory = new GenericIconHandlerFactory();
        $factory($container, GenericIconHandler::class);
    }
}
