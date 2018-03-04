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
            // @todo Does not work for deep arrays of input elements
            foreach ($exception->getValidatorMessages() as $element => $messages) {
                foreach ($messages as $message) {
                    $this->messageLogger->addError($element . ': ' . $message);
                }
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
}