<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Search\SearchCacheClearInterface;
use FactorioItemBrowser\Api\Server\Middleware\CleanupMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

/**
 * The PHPUnit test of the CleanupMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\CleanupMiddleware
 */
class CleanupMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked search cache clearer.
     * @var SearchCacheClearInterface&MockObject
     */
    protected $searchCacheClearer;

    /**
     * Seats up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->searchCacheClearer = $this->createMock(SearchCacheClearInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $middleware = new CleanupMiddleware($this->searchCacheClearer);

        $this->assertSame($this->searchCacheClearer, $this->extractProperty($middleware, 'searchCacheClearer'));
    }

    /**
     * Tests the process method.
     * @covers ::process
     */
    public function testProcess(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $this->searchCacheClearer->expects($this->once())
                                 ->method('clearExpiredResults');

        /* @var CleanupMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(CleanupMiddleware::class)
                           ->onlyMethods(['getRandomNumber'])
                           ->setConstructorArgs([$this->searchCacheClearer])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getRandomNumber')
                   ->with($this->identicalTo(1000))
                   ->willReturn(42);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the process method without matching the random number.
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcessWithoutRandomMatch(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $this->searchCacheClearer->expects($this->never())
                                 ->method('clearExpiredResults');

        /* @var CleanupMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(CleanupMiddleware::class)
                           ->onlyMethods(['getRandomNumber'])
                           ->setConstructorArgs([$this->searchCacheClearer])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getRandomNumber')
                   ->with($this->identicalTo(1000))
                   ->willReturn(21);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the getRandomNumber method.
     * @throws ReflectionException
     * @covers ::getRandomNumber
     */
    public function testGetRandomNumber(): void
    {
        $middleware = new CleanupMiddleware($this->searchCacheClearer);
        $result = $this->invokeMethod($middleware, 'getRandomNumber', 1000);

        $this->assertIsInt($result);
    }
}
