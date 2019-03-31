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
        if ($exception instanceof ApiServerException) {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        } else {
            $statusCode = 500;
            $message = 'Internal server error.';
        }

        if ($this->logger instanceof LoggerInterface && floor($statusCode / 100) === 5.) {
            $this->logger->crit((string) $exception);
        }

        $errorResponse = [
            'error' => [
                'message' => $message,
            ],
        ];
        return new JsonResponse($errorResponse, $statusCode, $response->getHeaders());
    }
}
