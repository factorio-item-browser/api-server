<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\CombinationNotFoundException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the CombinationNotFoundException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Exception\CombinationNotFoundException
 */
class CombinationNotFoundExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $combinationId = 'abc';
        $previous = $this->createMock(Throwable::class);

        $exception = new CombinationNotFoundException($combinationId, $previous);

        $this->assertSame('The combination with the id "abc" is not known.', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
