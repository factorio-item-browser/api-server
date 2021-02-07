<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when an invalid API key has been encountered.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InvalidApiKeyException extends ApiServerException
{
    private const MESSAGE = 'Invalid or missing API key.';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 401, $previous);
    }
}
