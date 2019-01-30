<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
use FactorioItemBrowser\Api\Server\Database\Service\CleanableServiceInterface;
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
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $cleanableServices = [
            $this->createMock(CleanableServiceInterface::class),
            $this->createMock(CleanableServiceInterface::class),
        ];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        /* @var CleanupMiddlewareFactory&MockObject $factory */
        $factory = $this->getMockBuilder(CleanupMiddlewareFactory::class)
                        ->setMethods(['getCleanableServices'])
                        ->getMock();
        $factory->expects($this->once())
                ->method('getCleanableServices')
                ->with($this->identicalTo($container))
                ->willReturn($cleanableServices);

        $factory($container, CleanupMiddleware::class);
    }

    /**
     * Tests the getCleanableServices method.
     * @throws ReflectionException
     * @covers ::getCleanableServices
     */
    public function testGetCleanableServices(): void
    {
        /* @var CachedSearchResultService&MockObject $cachedSearchResultService */
        $cachedSearchResultService = $this->createMock(CachedSearchResultService::class);

        $expectedResult = [
            $cachedSearchResultService,
        ];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo(CachedSearchResultService::class))
                  ->willReturn($cachedSearchResultService);

        $factory = new CleanupMiddlewareFactory();
        $result = $this->invokeMethod($factory, 'getCleanableServices', $container);

        $this->assertEquals($expectedResult, $result);
    }
}
