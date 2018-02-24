<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Error;

/**
 * A class logging messages to be inserted into the response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MessageLogger
{
    /**
     * The messages of the response.
     * @var array
     */
    protected $messages = [];

    /**
     * Adds an error to the response messages.
     * @param string $message
     */
    public function addError(string $message)
    {
        $this->messages[] = [
            'type' => 'error',
            'message' => $message
        ];
    }

    /**
     * Adds a warning to the response messages.
     * @param string $message
     */
    public function addWarning(string $message)
    {
        $this->messages[] = [
            'type' => 'warning',
            'message' => $message
        ];
    }

    /**
     * Adds an information to the response messages.
     * @param string $message
     */
    public function addInfo(string $message)
    {
        $this->messages[] = [
            'type' => 'info',
            'message' => $message
        ];
    }

    /**
     * Returns the messages for the response.
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}