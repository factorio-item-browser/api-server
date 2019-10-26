<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Middleware\ResponseSerializerMiddleware;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

/**
 * The PHPUnit test of the ResponseSerializerMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\ResponseSerializerMiddleware
 */
class ResponseSerializerMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $middleware = new ResponseSerializerMiddleware($this->serializer);

        $this->assertSame($this->serializer, $this->extractProperty($middleware, 'serializer'));
    }

    /**
     * Tests the process method.
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcess(): void
    {
        $serializedResponse = 'abc';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ClientResponseInterface&MockObject $clientResponse */
        $clientResponse = $this->createMock(ClientResponseInterface::class);
        /* @var ClientResponse&MockObject $modifiedResponse */
        $modifiedResponse = $this->createMock(ClientResponse::class);

        /* @var ClientResponse&MockObject $response */
        $response = $this->createMock(ClientResponse::class);
        $response->expects($this->once())
                 ->method('getResponse')
                 ->willReturn($clientResponse);
        $response->expects($this->once())
                 ->method('withSerializedResponse')
                 ->with($this->identicalTo($serializedResponse))
                 ->willReturn($modifiedResponse);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $this->serializer->expects($this->once())
                         ->method('serialize')
                         ->with($this->identicalTo($clientResponse), $this->identicalTo('json'))
                         ->willReturn($serializedResponse);

        $middleware = new ResponseSerializerMiddleware($this->serializer);
        $result = $middleware->process($request, $handler);

        $this->assertSame($modifiedResponse, $result);
    }

    /**
     * Tests the process method without an actual ClientResponse.
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcessWithoutClientResponse(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ClientResponse&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $this->serializer->expects($this->never())
                         ->method('serialize');

        $middleware = new ResponseSerializerMiddleware($this->serializer);
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
