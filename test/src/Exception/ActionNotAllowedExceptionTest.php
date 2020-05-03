<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\ActionNotAllowedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The PHPUnit test of the ActionNotAllowedException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Exception\ActionNotAllowedException
 */
class ActionNotAllowedExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var Throwable&MockObject $previous */
        $previous = $this->createMock(Throwable::class);

        $exception = new ActionNotAllowedException($previous);

        $this->assertSame('The requested action is not allowed with the current agent.', $exception->getMessage());
        $this->assertSame(403, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
