<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Response;

use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * The class generating the error response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ErrorResponseGenerator
{
    /**
     * The logger.
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Whether the debug mode is enabled.
     * @var bool
     */
    protected $isDebug;

    /**
     * Initializes the generator.
     * @param LoggerInterface $errorLogger
     * @param bool $isDebug
     */
    public function __construct(LoggerInterface $errorLogger, bool $isDebug)
    {
        $this->logger = $errorLogger;
        $this->isDebug = $isDebug;
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
    ): ResponseInterface {
        if ($exception instanceof ApiServerException) {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        } else {
            $statusCode = 500;
            $message = 'Internal server error.';
        }

        $this->logException($statusCode, $exception);

        $errorResponse = [
            'error' => $this->createResponseError($message, $exception)
        ];
        return new JsonResponse($errorResponse, $statusCode);
    }

    /**
     * Logs the exception if an actual logger is present.
     * @param int $statusCode
     * @param Throwable $exception
     */
    protected function logException(int $statusCode, Throwable $exception): void
    {
        if (floor($statusCode / 100) === 5.) {
            $this->logger->crit($exception);
        }
    }

    /**
     * Creates the error response data.
     * @param string $message
     * @param Throwable $exception
     * @return array<mixed>
     */
    protected function createResponseError(string $message, Throwable $exception): array
    {
        if ($this->isDebug) {
            $result = [
                'message' => $exception->getMessage(),
                'backtrace' => $exception->getTrace(),
            ];
        } else {
            $result = [
                'message' => $message,
            ];
        }

        return $result;
    }
}
