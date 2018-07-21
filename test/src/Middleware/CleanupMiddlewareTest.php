<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
use FactorioItemBrowser\Api\Server\Middleware\CleanupMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

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
     * Provides the data for the process test.
     * @return array
     */
    public function provideProcess(): array
    {
        return [
            [42, true],
            [21, false]
        ];
    }

    /**
     * Tests the process method.
     * @param int $randomNumber
     * @param bool $expectCleanup
     * @covers ::__construct
     * @covers ::process
     * @dataProvider provideProcess
     */
    public function testProcess(int $randomNumber, bool $expectCleanup)
    {
        /* @var CachedSearchResultService|MockObject $cachedSearchResultService */
        $cachedSearchResultService = $this->getMockBuilder(CachedSearchResultService::class)
                                          ->setMethods(['cleanup'])
                                          ->disableOriginalConstructor()
                                          ->getMock();
        $cachedSearchResultService->expects($expectCleanup ? $this->once() : $this->never())
                                  ->method('cleanup');

        /* @var ServerRequest $request */
        $request = $this->createMock(ServerRequest::class);
        /* @var Response $response */
        $response = $this->createMock(Response::class);
        /* @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                        ->setMethods(['handle'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

        /* @var CleanupMiddleware|MockObject $middleware */
        $middleware = $this->getMockBuilder(CleanupMiddleware::class)
                           ->setMethods(['getRandomNumber'])
                           ->setConstructorArgs([$cachedSearchResultService])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getRandomNumber')
                   ->with(1000)
                   ->willReturn($randomNumber);

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    /**
     * Tests the getRandomNumber method.
     * @covers ::getRandomNumber
     */
    public function testGetRandomNumber()
    {
        /* @var CachedSearchResultService $cachedSearchResultService */
        $cachedSearchResultService = $this->createMock(CachedSearchResultService::class);

        $middleware = new CleanupMiddleware($cachedSearchResultService);
        $result = $this->invokeMethod($middleware, 'getRandomNumber', 1000);
        $this->assertInternalType('int', $result);
    }
}
