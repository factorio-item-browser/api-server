<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Exception;

use Exception;
use Zend\Diactoros\Response\JsonResponse;

/**
 * The exception thrown when a request could not be validated.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ValidationException extends ApiServerException
{
    /**
     * The messages of the validator.
     * @var array
     */
    protected $validatorMessages = [];

    /**
     * Initializes the exception.
     * @param array $validatorMessages
     * @param Exception|null $previous
     */
    public function __construct(array $validatorMessages, Exception $previous = null)
    {
        parent::__construct(json_encode($validatorMessages, JsonResponse::DEFAULT_JSON_FLAGS), 400, $previous);
        $this->validatorMessages = $validatorMessages;
    }

    /**
     * Returns the messages of the validator.
     * @return array
     */
    public function getValidatorMessages(): array
    {
        return $this->validatorMessages;
    }
}