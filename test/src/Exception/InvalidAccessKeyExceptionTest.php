<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\InvalidAccessKeyException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the UnknownAgentException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Exception\InvalidAccessKeyException
 */
class InvalidAccessKeyExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var Throwable&MockObject $previous */
        $previous = $this->createMock(Throwable::class);

        $exception = new InvalidAccessKeyException($previous);

        $this->assertSame('Invalid access key.', $exception->getMessage());
        $this->assertSame(403, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
