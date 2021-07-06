<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\ApiEndpointNotFoundException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the ApiEndpointNotFoundException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Exception\ApiEndpointNotFoundException
 */
class ApiEndpointNotFoundExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $endpoint = 'abc';
        $previous = $this->createMock(Throwable::class);

        $exception = new ApiEndpointNotFoundException($endpoint, $previous);

        $this->assertSame('API endpoint not found: abc', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
