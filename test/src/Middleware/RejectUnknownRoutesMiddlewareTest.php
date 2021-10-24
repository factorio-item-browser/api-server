<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Exception\ApiEndpointNotFoundException;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Middleware\RejectUnknownRoutesMiddleware;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The PHPUnit test of the RejectUnknownRoutesMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Middleware\RejectUnknownRoutesMiddleware
 */
class RejectUnknownRoutesMiddlewareTest extends TestCase
{
    /**
     * @throws ServerException
     */
    public function testProcess(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $routeResult = $this->createMock(RouteResult::class);
        $routeResult->expects($this->any())
                    ->method('isSuccess')
                    ->willReturn(true);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->with($this->identicalTo(RouteResult::class))
                ->willReturn($routeResult);
        $request->expects($this->any())
                ->method('getRequestTarget')
                ->willReturn('foo');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $instance = new RejectUnknownRoutesMiddleware();
        $result = $instance->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * @throws ServerException
     */
    public function testProcessWithFailedRouteResult(): void
    {
        $routeResult = $this->createMock(RouteResult::class);
        $routeResult->expects($this->any())
                    ->method('isSuccess')
                    ->willReturn(false);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->with($this->identicalTo(RouteResult::class))
                ->willReturn($routeResult);
        $request->expects($this->any())
                ->method('getRequestTarget')
                ->willReturn('foo');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
                ->method('handle');

        $this->expectException(ApiEndpointNotFoundException::class);

        $instance = new RejectUnknownRoutesMiddleware();
        $instance->process($request, $handler);
    }

    /**
     * @throws ServerException
     */
    public function testProcessWithoutRouteResult(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->with($this->identicalTo(RouteResult::class))
                ->willReturn(null);
        $request->expects($this->any())
                ->method('getRequestTarget')
                ->willReturn('foo');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
                ->method('handle');

        $this->expectException(ApiEndpointNotFoundException::class);

        $instance = new RejectUnknownRoutesMiddleware();
        $instance->process($request, $handler);
    }
}
