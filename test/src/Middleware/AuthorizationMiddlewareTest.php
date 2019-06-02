<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Constant\RouteName;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\MissingAuthorizationTokenException;
use FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddleware;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

/**
 * The PHPUnit test of the AuthorizationMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddleware
 */
class AuthorizationMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked authorization service.
     * @var AuthorizationService&MockObject
     */
    protected $authorizationService;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationService = $this->createMock(AuthorizationService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $middleware = new AuthorizationMiddleware($this->authorizationService);

        $this->assertSame($this->authorizationService, $this->extractProperty($middleware, 'authorizationService'));
    }

    /**
     * Tests the process method.
     * @throws ApiServerException
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcess(): void
    {
        $matchedRouteName = 'abc';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ServerRequestInterface&MockObject $modifiedRequest */
        $modifiedRequest = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($modifiedRequest))
                ->willReturn($response);

        /* @var AuthorizationMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(AuthorizationMiddleware::class)
                           ->setMethods(['getMatchedRouteName', 'readAuthorizationFromRequest'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getMatchedRouteName')
                   ->with($this->identicalTo($request))
                   ->willReturn($matchedRouteName);
        $middleware->expects($this->once())
                   ->method('readAuthorizationFromRequest')
                   ->with($this->identicalTo($request))
                   ->willReturn($modifiedRequest);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the process method with a whitelisted route.
     * @throws ApiServerException
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcessWithWhitelistedRoute(): void
    {
        $matchedRouteName = RouteName::AUTH;

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        /* @var AuthorizationMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(AuthorizationMiddleware::class)
                           ->setMethods(['getMatchedRouteName', 'readAuthorizationFromRequest'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getMatchedRouteName')
                   ->with($this->identicalTo($request))
                   ->willReturn($matchedRouteName);
        $middleware->expects($this->never())
                   ->method('readAuthorizationFromRequest');

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the readAuthorizationFromRequest method.
     * @throws ReflectionException
     * @covers ::readAuthorizationFromRequest
     */
    public function testReadAuthorizationFromRequest(): void
    {
        $authorizationHeader = 'abc';
        $serializedToken = 'def';

        /* @var AuthorizationToken&MockObject $token */
        $token = $this->createMock(AuthorizationToken::class);
        /* @var ServerRequestInterface&MockObject $modifiedRequest */
        $modifiedRequest = $this->createMock(ServerRequestInterface::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with($this->identicalTo('Authorization'))
                ->willReturn($authorizationHeader);
        $request->expects($this->once())
                ->method('withAttribute')
                ->with($this->identicalTo(AuthorizationToken::class), $this->identicalTo($token))
                ->willReturn($modifiedRequest);

        $this->authorizationService->expects($this->once())
                                   ->method('deserializeToken')
                                   ->with($this->identicalTo($serializedToken))
                                   ->willReturn($token);

        /* @var AuthorizationMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(AuthorizationMiddleware::class)
                           ->setMethods(['extractSerializedTokenFromHeader'])
                           ->setConstructorArgs([$this->authorizationService])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('extractSerializedTokenFromHeader')
                   ->with($this->identicalTo($authorizationHeader))
                   ->willReturn($serializedToken);

        $result = $this->invokeMethod($middleware, 'readAuthorizationFromRequest', $request);

        $this->assertSame($modifiedRequest, $result);
    }

    /**
     * Tests the extractSerializedTokenFromHeader method.
     * @throws ReflectionException
     * @covers ::extractSerializedTokenFromHeader
     */
    public function testExtractSerializedTokenFromHeader(): void
    {
        $headerLine = 'Bearer abc';
        $expectedResult = 'abc';

        $middleware = new AuthorizationMiddleware($this->authorizationService);
        $result = $this->invokeMethod($middleware, 'extractSerializedTokenFromHeader', $headerLine);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the extractSerializedTokenFromHeader method.
     * @throws ReflectionException
     * @covers ::extractSerializedTokenFromHeader
     */
    public function testExtractSerializedTokenFromHeaderWithException(): void
    {
        $headerLine = 'abc';

        $this->expectException(MissingAuthorizationTokenException::class);

        $middleware = new AuthorizationMiddleware($this->authorizationService);
        $this->invokeMethod($middleware, 'extractSerializedTokenFromHeader', $headerLine);
    }
}
