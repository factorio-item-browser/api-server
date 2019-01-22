<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Middleware\AcceptLanguageMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

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
        /* @var ServerRequest|MockObject $request */
        $request = $this->getMockBuilder(ServerRequest::class)
                        ->setMethods(['getHeaderLine'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with('Accept-Language')
                ->willReturn($headerLine);

        /* @var TranslationService|MockObject $translationService */
        $translationService = $this->getMockBuilder(TranslationService::class)
                                   ->setMethods(['setCurrentLocale'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $translationService->expects($expectedLocale === null ? $this->never() : $this->once())
                           ->method('setCurrentLocale')
                           ->with($expectedLocale);

        /* @var Response $response */
        $response = $this->createMock(Response::class);

        /* @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                        ->setMethods(['handle'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

        $middleware = new AcceptLanguageMiddleware($translationService);
        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }
}
