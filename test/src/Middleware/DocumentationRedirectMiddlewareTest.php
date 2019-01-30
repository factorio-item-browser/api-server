<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use Blast\BaseUrl\BasePathHelper;
use FactorioItemBrowser\Api\Server\Middleware\DocumentationRedirectMiddleware;
use Fig\Http\Message\RequestMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * The PHPUnit test of the DocumentationRedirectMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\DocumentationRedirectMiddleware
 */
class DocumentationRedirectMiddlewareTest extends TestCase
{
    /**
     * Provides the data for the process test.
     * @return array
     */
    public function provideProcess(): array
    {
        return [
            [RequestMethodInterface::METHOD_GET, true],
            [RequestMethodInterface::METHOD_POST, false],
        ];
    }

    /**
     * Tests the process method.
     * @param string $requestMethod
     * @param bool $expectRedirect
     * @covers ::__construct
     * @covers ::process
     * @dataProvider provideProcess
     */
    public function testProcess(string $requestMethod, bool $expectRedirect): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getMethod')
                ->willReturn($requestMethod);

        /* @var BasePathHelper&MockObject $basePathHelper */
        $basePathHelper = $this->createMock(BasePathHelper::class);
        $basePathHelper->expects($expectRedirect ? $this->once() : $this->never())
                       ->method('__invoke')
                       ->with($this->identicalTo('/docs'))
                       ->willReturn('abc');

        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($expectRedirect ? $this->never() : $this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $middleware = new DocumentationRedirectMiddleware($basePathHelper);
        $result = $middleware->process($request, $handler);

        if ($expectRedirect) {
            $this->assertInstanceOf(RedirectResponse::class, $result);
        } else {
            $this->assertSame($response, $result);
        }
    }
}
