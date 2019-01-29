<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
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
     * Provides the data for the process test.
     * @return array
     */
    public function provideProcess(): array
    {
        return [
            ['/abc', true, false],
            ['/auth', false, true],
        ];
    }

    /**
     * Tests the process method.
     * @param string $requestTarget
     * @param bool $expectRead
     * @param bool $expectInitialRequest
     * @throws ApiServerException
     * @covers ::process
     * @dataProvider provideProcess
     */
    public function testProcess(string $requestTarget, bool $expectRead, bool $expectInitialRequest): void
    {
        /* @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getRequestTarget')
                ->willReturn($requestTarget);

        /* @var ServerRequestInterface|MockObject $modifiedRequest */
        $modifiedRequest = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($expectInitialRequest ? $request : $modifiedRequest))
                ->willReturn($response);

        /* @var AuthorizationMiddleware|MockObject $middleware */
        $middleware = $this->getMockBuilder(AuthorizationMiddleware::class)
                           ->setMethods(['readAuthorizationFromRequest'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $middleware->expects($expectRead ? $this->once() : $this->never())
                   ->method('readAuthorizationFromRequest')
                   ->with($this->identicalTo($request))
                   ->willReturn($modifiedRequest);

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
        $header = 'abc';
        $agentName = 'def';
        $enabledModCombinationIds = [42, 1337];
        $serializedToken = 'ghi';
        $token = new AuthorizationToken();
        $token->setAgentName($agentName)
              ->setEnabledModCombinationIds($enabledModCombinationIds);

        /* @var ServerRequestInterface|MockObject $modifiedRequest */
        $modifiedRequest = $this->createMock(ServerRequestInterface::class);

        /* @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with($this->identicalTo('Authorization'))
                ->willReturn($header);
        $request->expects($this->once())
                ->method('withAttribute')
                ->with($this->identicalTo('agent'), $this->identicalTo($agentName))
                ->willReturn($modifiedRequest);

        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);
        $authorizationService->expects($this->once())
                             ->method('deserializeToken')
                             ->with($serializedToken)
                             ->willReturn($token);

        /* @var ModService|MockObject $modService */
        $modService = $this->createMock(ModService::class);
        $modService->expects($this->once())
                   ->method('setEnabledModCombinationIds')
                   ->with($this->identicalTo($enabledModCombinationIds));

        /* @var AuthorizationMiddleware|MockObject $middleware */
        $middleware = $this->getMockBuilder(AuthorizationMiddleware::class)
                           ->setMethods(['extractSerializedTokenFromHeader'])
                           ->setConstructorArgs([$authorizationService, $modService])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('extractSerializedTokenFromHeader')
                   ->with($this->identicalTo($header))
                   ->willReturn($serializedToken);

        $result = $this->invokeMethod($middleware, 'readAuthorizationFromRequest', $request);
        $this->assertSame($modifiedRequest, $result);
    }

    /**
     * Provides the data for the extractSerializedTokenFromHeader test.
     * @return array
     */
    public function provideExtractSerializedTokenFromHeader(): array
    {
        return [
            ['Bearer abc', false, 'abc'],
            ['abc', true, null],
        ];
    }

    /**
     * Tests the extractSerializedTokenFromHeader method.
     * @param string $header
     * @param bool $expectException
     * @param string|null $expectedResult
     * @throws ReflectionException
     * @covers ::extractSerializedTokenFromHeader
     * @dataProvider provideExtractSerializedTokenFromHeader
     */
    public function testExtractSerializedTokenFromHeader(
        string $header,
        bool $expectException,
        ?string $expectedResult
    ): void {
        if ($expectException) {
            $this->expectException(MissingAuthorizationTokenException::class);
        }

        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);
        /* @var ModService|MockObject $modService */
        $modService = $this->createMock(ModService::class);

        $middleware = new AuthorizationMiddleware($authorizationService, $modService);
        $result = $this->invokeMethod($middleware, 'extractSerializedTokenFromHeader', $header);

        $this->assertSame($expectedResult, $result);
    }
}
