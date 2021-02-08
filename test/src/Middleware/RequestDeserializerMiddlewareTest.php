<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use Exception;
use FactorioItemBrowser\Api\Client\Request\AbstractRequest;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Exception\InvalidRequestBodyException;
use FactorioItemBrowser\Api\Server\Middleware\RequestDeserializerMiddleware;
use JMS\Serializer\SerializerInterface;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The PHPUnit test of the RequestDeserializerMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\RequestDeserializerMiddleware
 */
class RequestDeserializerMiddlewareTest extends TestCase
{
    /** @var SerializerInterface&MockObject */
    private SerializerInterface $serializer;
    /** @var array<string, class-string<AbstractRequest>> */
    private array $requestClassesByRoutes = [];

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return RequestDeserializerMiddleware&MockObject
     */
    private function createInstance(array $mockedMethods = []): RequestDeserializerMiddleware
    {
        return $this->getMockBuilder(RequestDeserializerMiddleware::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->serializer,
                        $this->requestClassesByRoutes,
                    ])
                    ->getMock();
    }

    /**
     * @throws ServerException
     */
    public function testProcess(): void
    {
        $matchedRouteName = 'abc';
        $combinationId = 'def';
        $locale = 'ghi';
        $bodyContents = 'jkl';
        $requestClass = SearchQueryRequest::class;

        $this->requestClassesByRoutes = [
            'abc' => SearchQueryRequest::class,
        ];

        $response = $this->createMock(ResponseInterface::class);

        $deserializedRequest = new SearchQueryRequest();
        $deserializedRequest->query = 'pqr';

        $expectedClientRequest = new SearchQueryRequest();
        $expectedClientRequest->query = 'pqr';
        $expectedClientRequest->combinationId = 'def';
        $expectedClientRequest->locale = 'ghi';

        $routeResult = $this->createMock(RouteResult::class);
        $routeResult->expects($this->any())
                    ->method('getMatchedRouteName')
                    ->willReturn($matchedRouteName);

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->any())
             ->method('getContents')
             ->willReturn($bodyContents);

        $request2 = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    [RouteResult::class, null, $routeResult],
                    ['combination-id', null, $combinationId],
                ]);
        $request->expects($this->any())
                ->method('getHeaderLine')
                ->willReturnMap([
                    ['Accept-Language', $locale],
                    ['Content-Type', 'application/json'],
                ]);
        $request->expects($this->any())
                ->method('getBody')
                ->willReturn($body);
        $request->expects($this->once())
                ->method('withParsedBody')
                ->with($this->equalTo($expectedClientRequest))
                ->willReturn($request2);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($bodyContents),
                             $this->identicalTo($requestClass),
                             $this->identicalTo('json'),
                         )
                         ->willReturn($deserializedRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request2))
                ->willReturn($response);

        $instance = $this->createInstance();
        $result = $instance->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * @throws ServerException
     */
    public function testProcessWithMissingContentType(): void
    {
        $matchedRouteName = 'abc';
        $combinationId = 'def';
        $locale = 'ghi';
        $requestClass = SearchQueryRequest::class;

        $this->requestClassesByRoutes = [
            'abc' => SearchQueryRequest::class,
        ];

        $response = $this->createMock(ResponseInterface::class);

        $deserializedRequest = new SearchQueryRequest();
        $deserializedRequest->query = 'pqr';

        $expectedClientRequest = new SearchQueryRequest();
        $expectedClientRequest->query = 'pqr';
        $expectedClientRequest->combinationId = 'def';
        $expectedClientRequest->locale = 'ghi';

        $routeResult = $this->createMock(RouteResult::class);
        $routeResult->expects($this->any())
                    ->method('getMatchedRouteName')
                    ->willReturn($matchedRouteName);

        $request2 = $this->createMock(ServerRequestInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    [RouteResult::class, null, $routeResult],
                    ['combination-id', null, $combinationId],
                ]);
        $request->expects($this->any())
                ->method('getHeaderLine')
                ->willReturnMap([
                    ['Accept-Language', $locale],
                    ['Content-Type', ''],
                ]);
        $request->expects($this->never())
                ->method('getBody');
        $request->expects($this->once())
                ->method('withParsedBody')
                ->with($this->equalTo($expectedClientRequest))
                ->willReturn($request2);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo('{}'),
                             $this->identicalTo($requestClass),
                             $this->identicalTo('json'),
                         )
                         ->willReturn($deserializedRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request2))
                ->willReturn($response);

        $instance = $this->createInstance();
        $result = $instance->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessWithSerializerException(): void
    {
        $matchedRouteName = 'abc';
        $combinationId = 'def';
        $locale = 'ghi';
        $bodyContents = 'jkl';
        $requestClass = SearchQueryRequest::class;

        $this->requestClassesByRoutes = [
            'abc' => SearchQueryRequest::class,
        ];

        $deserializedRequest = new SearchQueryRequest();
        $deserializedRequest->query = 'pqr';

        $expectedClientRequest = new SearchQueryRequest();
        $expectedClientRequest->query = 'pqr';
        $expectedClientRequest->combinationId = 'def';
        $expectedClientRequest->locale = 'ghi';

        $routeResult = $this->createMock(RouteResult::class);
        $routeResult->expects($this->any())
                    ->method('getMatchedRouteName')
                    ->willReturn($matchedRouteName);

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->any())
             ->method('getContents')
             ->willReturn($bodyContents);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->willReturnMap([
                    [RouteResult::class, null, $routeResult],
                    ['combination-id', null, $combinationId],
                ]);
        $request->expects($this->any())
                ->method('getHeaderLine')
                ->willReturnMap([
                    ['Accept-Language', $locale],
                    ['Content-Type', 'application/json'],
                ]);
        $request->expects($this->any())
                ->method('getBody')
                ->willReturn($body);
        $request->expects($this->never())
                ->method('withParsedBody');

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($bodyContents),
                             $this->identicalTo($requestClass),
                             $this->identicalTo('json'),
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
                ->method('handle');

        $this->expectException(InvalidRequestBodyException::class);

        $instance = $this->createInstance();
        $instance->process($request, $handler);
    }
}
