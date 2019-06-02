<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Throwable;

/**
 * The PHPUnit test of the EntityNotFoundException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException
 */
class EntityNotFoundExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $type = 'abc';
        $name = 'def';

        /* @var Throwable&MockObject $previous */
        $previous = $this->createMock(Throwable::class);

        $exception = new EntityNotFoundException($type, $name, $previous);

        $this->assertSame(
            'The abc def was not found or is not available in the currently enabled mods.',
            $exception->getMessage()
        );
        $this->assertSame(404, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
