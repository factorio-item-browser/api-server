<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Middleware\MetaMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
    public function testProcess(): void
    {
        $version = '1.2.3';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);

        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
                 ->method('withHeader')
                 ->withConsecutive(
                     [$this->identicalTo('Version'), $this->identicalTo($version)],
                     [$this->identicalTo('Runtime'), $this->isType('string')]
                 )
                 ->willReturnSelf();

        /* @var RequestHandlerInterface&MockObject $handler */
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
