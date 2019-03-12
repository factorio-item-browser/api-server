<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when a handler encountered a wrong request class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class UnexpectedRequestException extends ApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Expected request class %s, but got %s.';

    /**
     * Initializes the exception.
     * @param string $expectedRequestClass
     * @param string $actualRequestClass
     * @param Throwable|null $previous
     */
    public function __construct(string $expectedRequestClass, string $actualRequestClass, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $expectedRequestClass, $actualRequestClass), 500, $previous);
    }
}
