<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when an action is not allowed by the used agent.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ActionNotAllowedException extends ApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'The requested action is not allowed with the current agent.';

    /**
     * Initializes the exception.
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 403, $previous);
    }
}
