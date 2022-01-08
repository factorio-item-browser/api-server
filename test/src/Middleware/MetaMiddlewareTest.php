<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Middleware\MetaMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The PHPUnit test of the MetaMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Middleware\MetaMiddleware
 */
class MetaMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $version = '1.2.3';

        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
                 ->method('withHeader')
                 ->withConsecutive(
                     [$this->identicalTo('Version'), $this->identicalTo($version)],
                     [$this->identicalTo('Runtime'), $this->isType('string')]
                 )
                 ->willReturnSelf();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $middleware = new MetaMiddleware($version);
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
