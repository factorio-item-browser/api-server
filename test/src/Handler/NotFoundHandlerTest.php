<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler;

use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\NotFoundHandler;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

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
     */
    public function testHandle()
    {
        $this->expectException(ApiServerException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('API endpoint not found: /abc');

        $handler = new NotFoundHandler();
        $handler->handle((new ServerRequest())->withRequestTarget('/abc'));
    }
}
