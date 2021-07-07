<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\RejectedCombinationUpdateException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the RejectedCombinationUpdateException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Exception\RejectedCombinationUpdateException
 */
class RejectedCombinationUpdateExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $reason = 'abc';
        $previous = $this->createMock(Throwable::class);

        $exception = new RejectedCombinationUpdateException($reason, $previous);

        $this->assertSame('abc', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
