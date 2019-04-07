<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use Exception;
use FactorioItemBrowser\Api\Client\Request\RequestInterface;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\MalformedRequestException;
use FactorioItemBrowser\Api\Server\Middleware\RequestDeserializerMiddleware;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

/**
 * The PHPUnit test of the RequestDeserializerMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\RequestDeserializerMiddleware
 */
class RequestDeserializerMiddlewareTest extends TestCase
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
        $mapRouteToRequest = ['abc' => 'def'];

        $middleware = new RequestDeserializerMiddleware($this->serializer, $mapRouteToRequest);

        $this->assertSame($this->serializer, $this->extractProperty($middleware, 'serializer'));
        $this->assertSame($mapRouteToRequest, $this->extractProperty($middleware, 'mapRouteToRequest'));
    }

    /**
     * Tests the process method.
     * @throws ApiServerException
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcess(): void
    {
        $routeName = 'abc';
        $mapRouteToRequest = ['abc' => 'def'];
        $expectedRequestClass = 'def';

        /* @var RequestInterface&MockObject $clientRequest */
        $clientRequest = $this->createMock(RequestInterface::class);
        /* @var ServerRequestInterface&MockObject $modifiedRequest */
        $modifiedRequest = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('withAttribute')
                ->with($this->identicalTo(RequestInterface::class), $this->identicalTo($clientRequest))
                ->willReturn($modifiedRequest);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($modifiedRequest))
                ->willReturn($response);

        /* @var RequestDeserializerMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(RequestDeserializerMiddleware::class)
                           ->setMethods(['getMatchedRouteName', 'deserializeRequestBody'])
                           ->setConstructorArgs([$this->serializer, $mapRouteToRequest])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getMatchedRouteName')
                   ->with($this->identicalTo($request))
                   ->willReturn($routeName);
        $middleware->expects($this->once())
                   ->method('deserializeRequestBody')
                   ->with($this->identicalTo($request), $this->identicalTo($expectedRequestClass))
                   ->willReturn($clientRequest);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the process method without a match for deserializing the request.
     * @throws ApiServerException
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcessWithoutMatch(): void
    {
        $routeName = 'abc';
        $mapRouteToRequest = [];

        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->never())
                ->method('withAttribute');

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        /* @var RequestDeserializerMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(RequestDeserializerMiddleware::class)
                           ->setMethods(['getMatchedRouteName', 'deserializeRequestBody'])
                           ->setConstructorArgs([$this->serializer, $mapRouteToRequest])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getMatchedRouteName')
                   ->with($this->identicalTo($request))
                   ->willReturn($routeName);
        $middleware->expects($this->never())
                   ->method('deserializeRequestBody');

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the deserializeRequestBody method.
     * @throws ReflectionException
     * @covers ::deserializeRequestBody
     */
    public function testDeserializeRequestBody(): void
    {
        $requestBody = '{"abc":"def"}';
        $requestClass = 'ghi';

        /* @var RequestInterface&MockObject $clientRequest */
        $clientRequest = $this->createMock(RequestInterface::class);

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($requestBody),
                             $this->identicalTo($requestClass),
                             $this->identicalTo('json')
                         )
                         ->willReturn($clientRequest);

        $middleware = new RequestDeserializerMiddleware($this->serializer, []);
        $result = $this->invokeMethod($middleware, 'deserializeRequestBody', $request, $requestClass);

        $this->assertSame($clientRequest, $result);
    }

    /**
     * Tests the deserializeRequestBody method With empty request.
     * @throws ReflectionException
     * @covers ::deserializeRequestBody
     */
    public function testDeserializeRequestBodyWithEmptyRequest(): void
    {
        $requestBody = '';
        $expectedRequestBody = '{}';
        $requestClass = 'ghi';

        /* @var RequestInterface&MockObject $clientRequest */
        $clientRequest = $this->createMock(RequestInterface::class);

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($expectedRequestBody),
                             $this->identicalTo($requestClass),
                             $this->identicalTo('json')
                         )
                         ->willReturn($clientRequest);

        $middleware = new RequestDeserializerMiddleware($this->serializer, []);
        $result = $this->invokeMethod($middleware, 'deserializeRequestBody', $request, $requestClass);

        $this->assertSame($clientRequest, $result);
    }

    /**
     * Tests the deserializeRequestBody method.
     * @throws ReflectionException
     * @covers ::deserializeRequestBody
     */
    public function testDeserializeRequestBodyWithMalformedRequest(): void
    {
        $requestBody = '{"abc":"def"}';
        $requestClass = 'ghi';

        /* @var StreamInterface&MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
             ->method('getContents')
             ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($body);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($requestBody),
                             $this->identicalTo($requestClass),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(MalformedRequestException::class);

        $middleware = new RequestDeserializerMiddleware($this->serializer, []);
        $this->invokeMethod($middleware, 'deserializeRequestBody', $request, $requestClass);
    }
}
