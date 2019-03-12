<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Throwable;

/**
 * The exception thrown when the requested API endpoint was not found.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ApiEndpointNotFoundException extends ApiServerException
{
    /**
     * The message template of the exception.
     */
    protected const MESSAGE = 'API endpoint not found: %s';

    /**
     * Initializes the exception.
     * @param string $endpoint
     * @param Throwable|null $previous
     */
    public function __construct(string $endpoint, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $endpoint), 404, $previous);
    }
}
