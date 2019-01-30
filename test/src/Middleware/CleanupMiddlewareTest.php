<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\CleanableServiceInterface;
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
     * Tests the process method.
     * @covers ::__construct
     * @covers ::process
     */
    public function testProcess(): void
    {
        /* @var CleanableServiceInterface&MockObject $service1 */
        $service1 = $this->createMock(CleanableServiceInterface::class);
        $service1->expects($this->once())
                 ->method('cleanup');

        /* @var CleanableServiceInterface&MockObject $service2 */
        $service2 = $this->createMock(CleanableServiceInterface::class);
        $service2->expects($this->never())
                 ->method('cleanup');

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

        /* @var CleanupMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(CleanupMiddleware::class)
                           ->setMethods(['getRandomNumber'])
                           ->setConstructorArgs([[$service1, $service2]])
                           ->getMock();
        $middleware->expects($this->exactly(2))
                   ->method('getRandomNumber')
                   ->with($this->identicalTo(1000))
                   ->willReturnOnConsecutiveCalls(
                       42,
                       21
                   );

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
        $middleware = new CleanupMiddleware([]);
        $result = $this->invokeMethod($middleware, 'getRandomNumber', 1000);

        $this->assertIsInt($result);
    }
}
