<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Search\SearchCacheClearInterface;
use FactorioItemBrowser\Api\Server\Middleware\CleanupMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The PHPUnit test of the CleanupMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Middleware\CleanupMiddleware
 */
class CleanupMiddlewareTest extends TestCase
{
    /** @var SearchCacheClearInterface&MockObject */
    private SearchCacheClearInterface $searchCacheClearer;

    protected function setUp(): void
    {
        $this->searchCacheClearer = $this->createMock(SearchCacheClearInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return CleanupMiddleware&MockObject
     */
    private function createInstance(array $mockedMethods = []): CleanupMiddleware
    {
        return $this->getMockBuilder(CleanupMiddleware::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->searchCacheClearer,
                    ])
                    ->getMock();
    }

    /**
     * @return array<mixed>
     */
    public function provideProcess(): array
    {
        return [
            [7500, true], // mt_rand will roll 42 on first call.
            [7501, false],
        ];
    }

    /**
     * @param int $seed
     * @param bool $expectClear
     * @dataProvider provideProcess
     */
    public function testProcess(int $seed, bool $expectClear): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        $this->searchCacheClearer->expects($expectClear ? $this->once() : $this->never())
                                 ->method('clearExpiredResults');

        $instance = $this->createInstance();

        mt_srand($seed);
        $result = $instance->process($request, $handler);

        $this->assertSame($response, $result);
    }
}
