<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Response;

use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Log\LoggerInterface;

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
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Initializes the generator.
     * @param LoggerInterface|null $logger
     */
    public function __construct(?LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $statusCode = $exception->getCode();
        $message = $exception->getMessage();
        if ($statusCode < 400 || $statusCode >= 600) {
            $statusCode = 500;
            $message = 'An unexpected error occurred.';

            if ($this->logger instanceof LoggerInterface) {
                $this->logger->crit($exception);
            }
        }

        $errorData = [
            'message' => $message,
        ];
        if ($exception instanceof ApiServerException && count($exception->getParameters()) > 0) {
            $errorData['parameters'] = $exception->getParameters();
        }

        return new JsonResponse(['error' => $errorData], $statusCode, $response->getHeaders());
    }
}
