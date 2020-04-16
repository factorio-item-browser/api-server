<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler;

use FactorioItemBrowser\Api\Server\Exception\ApiEndpointNotFoundException;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\NotFoundHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the NotFoundHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\NotFoundHandler
 */
class NotFoundHandlerTest extends TestCase
{
    /**
     * Tests the handle method.
     * @covers ::handle
     * @throws ApiServerException
     */
    public function testHandle(): void
    {
        $requestTarget = 'abc';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getRequestTarget')
                ->willReturn($requestTarget);

        $this->expectException(ApiEndpointNotFoundException::class);
        $this->expectExceptionMessage('API endpoint not found: abc');

        $handler = new NotFoundHandler();
        $handler->handle($request);
    }
}
