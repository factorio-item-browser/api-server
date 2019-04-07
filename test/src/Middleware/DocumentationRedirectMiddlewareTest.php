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
use ReflectionException;
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
     * Tests the process method.
     * @throws ReflectionException
     * @covers ::__construct
     * @covers ::process
     */
    public function testProcess(): void
    {
        $requestMethod = RequestMethodInterface::METHOD_POST;

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getMethod')
                ->willReturn($requestMethod);

        /* @var BasePathHelper&MockObject $basePathHelper */
        $basePathHelper = $this->createMock(BasePathHelper::class);
        $basePathHelper->expects($this->never())
                       ->method('__invoke');

        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $middleware = new DocumentationRedirectMiddleware($basePathHelper);
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the process method with a redirect.
     * @throws ReflectionException
     * @covers ::__construct
     * @covers ::process
     */
    public function testProcessWithRedirect(): void
    {
        $requestMethod = RequestMethodInterface::METHOD_GET;

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getMethod')
                ->willReturn($requestMethod);

        /* @var BasePathHelper&MockObject $basePathHelper */
        $basePathHelper = $this->createMock(BasePathHelper::class);
        $basePathHelper->expects($this->once())
                       ->method('__invoke')
                       ->with($this->identicalTo('/docs'))
                       ->willReturn('abc');

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
                ->method('handle');

        $middleware = new DocumentationRedirectMiddleware($basePathHelper);
        $result = $middleware->process($request, $handler);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
