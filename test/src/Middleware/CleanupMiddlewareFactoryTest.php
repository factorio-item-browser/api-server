<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
use FactorioItemBrowser\Api\Server\Middleware\CleanupMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\CleanupMiddlewareFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the CleanupMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\CleanupMiddlewareFactory
 */
class CleanupMiddlewareFactoryTest extends TestCase
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
        $container->expects($this->once())
                  ->method('get')
                  ->with(CachedSearchResultService::class)
                  ->willReturn($this->createMock(CachedSearchResultService::class));

        $factory = new CleanupMiddlewareFactory();
        $result = $factory($container, CleanupMiddleware::class);
        $this->assertInstanceOf(CleanupMiddleware::class, $result);
    }
}
