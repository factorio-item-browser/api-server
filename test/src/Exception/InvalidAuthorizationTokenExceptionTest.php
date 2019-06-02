<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\InvalidAuthorizationTokenException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Throwable;

/**
 * The PHPUnit test of the InvalidAuthorizationTokenException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Exception\InvalidAuthorizationTokenException
 */
class InvalidAuthorizationTokenExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var Throwable&MockObject $previous */
        $previous = $this->createMock(Throwable::class);

        $exception = new InvalidAuthorizationTokenException($previous);

        $this->assertSame('The authorization token is invalid.', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
