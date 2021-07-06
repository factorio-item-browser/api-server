<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when a not known combination id has been encountered.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationNotFoundException extends ServerException
{
    private const MESSAGE = 'The combination with the id "%s" is not known.';

    public function __construct(string $combinationId, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $combinationId), 404, $previous);
    }
}
