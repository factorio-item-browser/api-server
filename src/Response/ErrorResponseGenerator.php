<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Response;

use FactorioItemBrowser\Api\Server\Exception\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zend\Diactoros\Response\JsonResponse;

/**
 * The class generating the error response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ErrorResponseGenerator
{
    /**
     * The message logger.
     * @var MessageLogger
     */
    protected $messageLogger;

    /**
     * Initializes the meta middleware.
     * @param MessageLogger $messageLogger
     */
    public function __construct(MessageLogger $messageLogger)
    {
        $this->messageLogger = $messageLogger;
    }

    /**
     * Handles the thrown exception.
     * @param Throwable $exception
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(
        Throwable $exception,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $statusCode = $exception->getCode();
        if ($exception instanceof ValidationException) {
            foreach ($this->flattenValidatorMessages($exception->getValidatorMessages(), '') as $element => $message) {
                $this->messageLogger->addError(trim($element, '/') . ': ' . $message);
            }
        } else {
            if ($statusCode >= 400 && $statusCode < 600) {
                $this->messageLogger->addError($exception->getMessage());
            } else {
                $statusCode = 500;
                $this->messageLogger->addError('An unexpected error occurred.');
                // @todo Remove debug info or gate it behind a config value.
                $this->messageLogger->addInfo(get_class($exception));
                $this->messageLogger->addInfo($exception->getMessage());
            }
        }
        return new JsonResponse([], $statusCode, $response->getHeaders());
    }

    /**
     * Flattens the validator messages to a simple array.
     * @param array $messages
     * @param string $currentKey
     * @return array|string[]
     */
    protected function flattenValidatorMessages(array $messages, string $currentKey): array
    {
        $result = [];
        foreach ($messages as $key => $message) {
            if (is_array($message)) {
                $result = array_merge(
                    $result,
                    $this->flattenValidatorMessages($message, $currentKey . '/' . $key)
                );
            } else {
                $result[$currentKey] = $message;
            }
        }
        return $result;
    }
}