<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Exception;

use Exception;
use FactorioItemBrowser\Api\Server\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ValidationException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Exception\ValidationException
 */
class ValidationExceptionTest extends TestCase
{
    /**
     * Tests the processValidatorMessages method.
     * @covers ::__construct
     * @covers ::processValidatorMessages
     */
    public function testProcessValidatorMessages(): void
    {
        $previous = new Exception('foo');
        $validatorMessages = [
            'abc' => [
                'def' => 'ghi',
                'jkl' => 'mno'
            ],
            'pqr' => [
                'stu' => [
                    'vwx' => 'yz'
                ]
            ]
        ];
        $expectedParameters = [
            [
                'name' => 'abc',
                'message' => 'ghi'
            ],
            [
                'name' => 'abc',
                'message' => 'mno'
            ],
            [
                'name' => 'pqr|stu',
                'message' => 'yz'
            ]
        ];

        $exception = new ValidationException($validatorMessages, $previous);
        $this->assertSame('Request validation failed.', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame($expectedParameters, $exception->getParameters());
    }
}
