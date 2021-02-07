<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the EntityNotFoundException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException
 */
class EntityNotFoundExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $type = 'abc';
        $name = 'def';
        $previous = $this->createMock(Throwable::class);

        $exception = new EntityNotFoundException($type, $name, $previous);

        $this->assertSame(
            'The abc def was not found or is not available in the current combination of mods.',
            $exception->getMessage()
        );
        $this->assertSame(404, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
