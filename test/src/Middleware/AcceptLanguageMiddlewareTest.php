<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Middleware\AcceptLanguageMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The PHPUnit test of the AcceptLanguageMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\AcceptLanguageMiddleware
 */
class AcceptLanguageMiddlewareTest extends TestCase
{
    /**
     * Provides the data for the process test.
     * @return array
     */
    public function provideProcess(): array
    {
        return [
            ['abc', 'abc'],
            ['', null],
        ];
    }

    /**
     * Tests the process method.
     * @param string $headerLine
     * @param string|null $expectedLocale
     * @covers ::__construct
     * @covers ::process
     * @dataProvider provideProcess
     */
    public function testProcess(string $headerLine, ?string $expectedLocale): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with($this->identicalTo('Accept-Language'))
                ->willReturn($headerLine);

        /* @var TranslationService&MockObject $translationService */
        $translationService = $this->createMock(TranslationService::class);
        $translationService->expects($expectedLocale === null ? $this->never() : $this->once())
                           ->method('setCurrentLocale')
                           ->with($this->identicalTo($expectedLocale));

        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $middleware = new AcceptLanguageMiddleware($translationService);
        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
