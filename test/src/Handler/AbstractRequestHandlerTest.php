<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Request\RequestInterface as ClientRequestInterface;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\UnexpectedRequestException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * The PHPUnit test of the AbstractRequestHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler
 */
class AbstractRequestHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the handle method.
     * @throws ApiServerException
     * @throws ReflectionException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ClientRequestInterface&MockObject $clientRequest */
        $clientRequest = $this->createMock(ClientRequestInterface::class);
        /* @var ClientResponseInterface&MockObject $clientResponse */
        $clientResponse = $this->createMock(ClientResponseInterface::class);

        /* @var AbstractRequestHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRequestHandler::class)
                        ->setMethods(['getClientRequest', 'handleRequest'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('getClientRequest')
                ->with($this->identicalTo($request))
                ->willReturn($clientRequest);
        $handler->expects($this->once())
                ->method('handleRequest')
                ->with($this->identicalTo($clientRequest))
                ->willReturn($clientResponse);

        $result = $handler->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertSame($clientResponse, $result->getResponse());
    }

    /**
     * Tests the getClientRequest method.
     * @throws ReflectionException
     * @covers ::getClientRequest
     */
    public function testGetClientRequest(): void
    {
        $expectedRequestClass = 'MockedClientRequestInterface';

        /* @var ClientRequestInterface&MockObject $clientRequest */
        $clientRequest = $this->getMockBuilder(ClientRequestInterface::class)
                              ->setMockClassName($expectedRequestClass)
                              ->getMockForAbstractClass();

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(ClientRequestInterface::class))
                ->willReturn($clientRequest);

        /* @var AbstractRequestHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRequestHandler::class)
                        ->setMethods(['getExpectedRequestClass'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('getExpectedRequestClass')
                ->willReturn($expectedRequestClass);

        $result = $this->invokeMethod($handler, 'getClientRequest', $request);

        $this->assertSame($clientRequest, $result);
    }

    /**
     * Tests the getClientRequest method with an unexpected request.
     * @throws ReflectionException
     * @covers ::getClientRequest
     */
    public function testGetClientRequestWithUnexpectedRequest(): void
    {
        $expectedRequestClass = 'MockedClientRequestInterface';

        /* @var ClientRequestInterface&MockObject $clientRequest */
        $clientRequest = $this->createMock(ClientRequestInterface::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(ClientRequestInterface::class))
                ->willReturn($clientRequest);

        $this->expectException(UnexpectedRequestException::class);

        /* @var AbstractRequestHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRequestHandler::class)
                        ->setMethods(['getExpectedRequestClass'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('getExpectedRequestClass')
                ->willReturn($expectedRequestClass);

        $this->invokeMethod($handler, 'getClientRequest', $request);
    }

    /**
     * Tests the getClientRequest method without a request.
     * @throws ReflectionException
     * @covers ::getClientRequest
     */
    public function testGetClientRequestWithoutRequest(): void
    {
        $expectedRequestClass = 'MockedClientRequestInterface';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(ClientRequestInterface::class))
                ->willReturn(null);

        $this->expectException(UnexpectedRequestException::class);

        /* @var AbstractRequestHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AbstractRequestHandler::class)
                        ->setMethods(['getExpectedRequestClass'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('getExpectedRequestClass')
                ->willReturn($expectedRequestClass);

        $this->invokeMethod($handler, 'getClientRequest', $request);
    }
}
