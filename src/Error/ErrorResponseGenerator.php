<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Error;

use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
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
        if ($exception instanceof ApiServerException) {
            $statusCode = $exception->getCode();
            $this->messageLogger->addError($exception->getMessage());
        } else {
            $statusCode = 500;
            $this->messageLogger->addError('An unexpected error occurred.');
        }
        return new JsonResponse([], $statusCode);
    }
}