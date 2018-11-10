<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * The PHPUnit test of the AuthorizationMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddleware
 */
class AuthorizationMiddlewareTest extends TestCase
{
    /**
     * Provides the data for the process test.
     * @return array
     */
    public function provideProcess(): array
    {
        $validHeader = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjEyMzQ1Njc4OTAsImV4cCI6MjE0NzQ4MzY0Nywi'
            . 'YWd0IjoiYWJjIiwibWRzIjpbNDIsMTMzN119.uq1IPDEuqkQzOFqsTGDtNK7D6Cd8sb3eMR-j_BkTlPw';

        $invalidHeader1 = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjEyMzQ1Njc4OTAsImV4cCI6MTIzNDU2Nzg5MCwi'
            . 'YWd0IjoiYWJjIiwibWRzIjpbNDIsMTMzN10sImltcCI6MX0.2_ziraMpcLhYMCLpX5OQm75V6KQdnAmqJbNwgYz4VJM';
        // Invalid signature
        $invalidHeader2 = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjEyMzQ1Njc4OTAsImV4cCI6MjE0NzQ4MzY0Nywi'
            . 'YWd0IjoiYWJjIiwibWRzIjpbNDIsMTMzN10sImltcCI6MX0.qfIlOQ_rNfSqmBeTpkI7gbof4PTdO1kVzGuLnFDFudA';

        return [
            ['/auth', null, '', null, null],
            ['/wuppdi', $validHeader, '', [42, 1337], 'abc'],
            ['/wuppdi', $invalidHeader1, 'Authorization token is invalid.', null, null],
            ['/wuppdi', $invalidHeader2, 'Authorization token is invalid.', null, null],
            ['/wuppdi', 'FAIL', 'Authorization token is missing.', null, null],
        ];
    }

    /**
     * Tests the process method.
     * @param string $requestTarget
     * @param null|string $authorizationHeader
     * @param string $expectedExceptionMessage
     * @param array|null $expectedModCombinationIds
     * @param null|string $expectedAgent
     * @covers ::__construct
     * @covers ::process
     * @dataProvider provideProcess
     */
    public function testProcess(
        string $requestTarget,
        ?string $authorizationHeader,
        string $expectedExceptionMessage,
        ?array $expectedModCombinationIds,
        ?string $expectedAgent
    ) {
        if (strlen($expectedExceptionMessage) > 0) {
            $this->expectException(ApiServerException::class);
            $this->expectExceptionCode(401);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $authorizationKey = 'foo';

        /* @var ModService|MockObject $modService */
        $modService = $this->getMockBuilder(ModService::class)
                           ->setMethods(['setEnabledModCombinationIds'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $modService->expects($expectedModCombinationIds === null ? $this->never() : $this->once())
                   ->method('setEnabledModCombinationIds')
                   ->with($expectedModCombinationIds);

        /* @var ServerRequest|MockObject $request */
        $request = $this->getMockBuilder(ServerRequest::class)
                        ->setMethods(['getRequestTarget', 'getHeaderLine', 'withAttribute'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $request->expects($this->once())
                ->method('getRequestTarget')
                ->willReturn($requestTarget);
        $request->expects($authorizationHeader === null ? $this->never() : $this->once())
                ->method('getHeaderLine')
                ->with('Authorization')
                ->willReturn($authorizationHeader);
        $request->expects($expectedAgent === null ? $this->never() : $this->once())
                ->method('withAttribute')
                ->with('agent', $expectedAgent)
                ->willReturnSelf();

        /* @var Response $response */
        $response = $this->createMock(Response::class);
        /* @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                        ->setMethods(['handle'])
                        ->getMockForAbstractClass();
        $handler->expects(strlen($expectedExceptionMessage) === 0 ? $this->once() : $this->never())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

        $middleware = new AuthorizationMiddleware($authorizationKey, $modService);
        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }
}
