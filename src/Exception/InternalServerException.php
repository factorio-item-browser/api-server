<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when something went really wrong, i.e. mis-configured project.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class InternalServerException extends ServerException
{
    private const MESSAGE = 'Internal server error: %s';

    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $message), 500, $previous);
    }
}
