<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ApiServerException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Exception\ApiServerException
 */
class ApiServerExceptionTest extends TestCase
{
    /**
     * Tests the adding and getting parameters.
     * @covers ::addParameter
     * @covers ::getParameters
     */
    public function testAddAndGetParameters()
    {
        $exception = new ApiServerException();
        $this->assertSame([], $exception->getParameters());
        $this->assertSame($exception, $exception->addParameter('abc', 'def'));
        $this->assertSame([['name' => 'abc', 'message' => 'def']], $exception->getParameters());
    }
}
