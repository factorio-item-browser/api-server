<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when an entity cannot be found.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class EntityNotFoundException extends ApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'The %s %s was not found or is not available in the currently enabled mods.';

    /**
     * Initializes the exception.
     * @param string $type
     * @param string $name
     * @param Throwable|null $previous
     */
    public function __construct(string $type, string $name, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $type, $name), 404, $previous);
    }
}
