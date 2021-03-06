<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\InternalServerException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the InternalServerException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Exception\InternalServerException
 */
class InternalServerExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $message = 'abc';
        $previous = $this->createMock(Throwable::class);

        $exception = new InternalServerException($message, $previous);

        $this->assertSame('Internal server error: abc', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
