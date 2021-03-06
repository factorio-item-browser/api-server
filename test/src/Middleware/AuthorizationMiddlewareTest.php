<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Server\Exception\InvalidApiKeyException;
use FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The PHPUnit test of the AuthorizationMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddleware
 */
class AuthorizationMiddlewareTest extends TestCase
{
    /** @var array<array{name: string, api-key: string}> */
    private array $agents = [
        ['name' => 'foo', 'api-key' => 'bar'],
    ];

    /**
     * @param array<string> $mockedMethods
     * @return AuthorizationMiddleware&MockObject
     */
    private function createInstance(array $mockedMethods = []): AuthorizationMiddleware
    {
        return $this->getMockBuilder(AuthorizationMiddleware::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->agents,
                    ])
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideProcess(): array
    {
        return [
            ['bar', 'abc', false],
            ['bar', '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76', false],
            ['baz', 'abc', true],
            ['baz', '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76', false],
            ['', 'abc', true],
            ['', '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76', false],
        ];
    }

    /**
     * @param string $apiKey
     * @param string $combinationId
     * @param bool $expectException
     * @throws InvalidApiKeyException
     * @dataProvider provideProcess
     */
    public function testProcess(string $apiKey, string $combinationId, bool $expectException): void
    {
        $clientRequest = new SearchQueryRequest();
        $clientRequest->combinationId = $combinationId;

        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with($this->identicalTo('Api-Key'))
                ->willReturn($apiKey);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($expectException ? $this->never() : $this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        if ($expectException) {
            $this->expectException(InvalidApiKeyException::class);
        }

        $instance = $this->createInstance();
        $result = $instance->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
