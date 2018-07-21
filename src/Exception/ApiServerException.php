<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Exception;

/**
 * The exception class thrown by the API server.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ApiServerException extends Exception
{
    /**
     * The request parameters which caused the exception.
     * @var array|string[]
     */
    protected $parameters = [];

    /**
     * Adds a request parameter which caused the exception.
     * @param string $name
     * @param string $message
     * @return $this
     */
    public function addParameter(string $name, string $message)
    {
        $this->parameters[] = [
            'name' => $name,
            'message' => $message
        ];
        return $this;
    }

    /**
     * Returns the request parameters which caused the exception.
     * @return array|string[][]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
