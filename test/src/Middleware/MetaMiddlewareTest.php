<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Middleware\MetaMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * The PHPUnit test of the MetaMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\MetaMiddleware
 */
class MetaMiddlewareTest extends TestCase
{
    /**
     * Tests the process method.
     * @covers ::__construct
     * @covers ::process
     */
    public function testProcess()
    {
        $version = '1.2.3';

        /* @var ServerRequest $request */
        $request = $this->createMock(ServerRequest::class);

        /* @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)
                         ->setMethods(['withHeader'])
                         ->disableOriginalConstructor()
                         ->getMock();
        $response->expects($this->exactly(2))
                 ->method('withHeader')
                 ->withConsecutive(
                     ['X-Version', $version],
                     ['X-Runtime', $this->isType('string')]
                 )
                 ->willReturnSelf();


        /* @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                        ->setMethods(['handle'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

        $middleware = new MetaMiddleware($version);
        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }
}
