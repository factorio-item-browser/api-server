<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Search\SearchCacheClearInterface;
use FactorioItemBrowser\Api\Server\Middleware\CleanupMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\CleanupMiddlewareFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the CleanupMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\CleanupMiddlewareFactory
 */
class CleanupMiddlewareFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var SearchCacheClearInterface&MockObject $searchCacheClearer */
        $searchCacheClearer = $this->createMock(SearchCacheClearInterface::class);

        $expectedResult = new CleanupMiddleware($searchCacheClearer);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo(SearchCacheClearInterface::class))
                  ->willReturn($searchCacheClearer);

        $factory = new CleanupMiddlewareFactory();
        $result = $factory($container, CleanupMiddleware::class);

        $this->assertEquals($expectedResult, $result);
    }
}
