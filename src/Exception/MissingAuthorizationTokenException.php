<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when the authorization token is missing.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MissingAuthorizationTokenException extends ApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'The authorization token is missing.';

    /**
     * Initializes the exception.
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 401, $previous);
    }
}
