<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Exception;

/**
 * The exception thrown when a request could not be validated.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ValidationException extends ApiServerException
{
    /**
     * Initializes the exception.
     * @param array $validatorMessages
     * @param Exception|null $previous
     */
    public function __construct(array $validatorMessages, Exception $previous = null)
    {
        parent::__construct('Request validation failed.', 400, $previous);
        $this->processValidatorMessages($validatorMessages, '');
    }

    /**
     * Processes the validator messages.
     * @param array $validatorMessages
     * @param string $key
     * @return $this
     */
    protected function processValidatorMessages(array $validatorMessages, string $key)
    {
        foreach ($validatorMessages as $name => $validatorMessage) {
            if (is_array($validatorMessage)) {
                $fullKey = ltrim($key . '|' . $name, '|');
                $this->processValidatorMessages($validatorMessage, $fullKey);
            } else {
                $this->addParameter($key, (string) $validatorMessage);
            }
        }
        return $this;
    }
}