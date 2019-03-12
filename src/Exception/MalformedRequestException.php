<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when a request is malformed and cannot be deserialized.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MalformedRequestException extends ApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'Malformed request: %s';

    /**
     * Initializes the exception.
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $message), 400, $previous);
    }
}
