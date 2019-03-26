<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when a recipe cannot be found.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeNotFoundException extends ApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'The recipe was not found or is not available in the currently enabled mods.';

    /**
     * Initializes the exception.
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 404, $previous);
    }
}
