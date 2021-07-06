<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Exception;
use FactorioItemBrowser\Api\Client\Request\AbstractRequest;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Exception\CombinationNotFoundException;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The middleware verifying the combination.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationMiddleware implements MiddlewareInterface
{
    private CombinationRepository $combinationRepository;

    public function __construct(CombinationRepository $combinationRepository)
    {
        $this->combinationRepository = $combinationRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ServerException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var AbstractRequest $clientRequest */
        $clientRequest = $request->getParsedBody();
        try {
            $combinationId = Uuid::fromString($clientRequest->combinationId);
        } catch (Exception $e) {
            throw new CombinationNotFoundException($clientRequest->combinationId, $e);
        }

        $combination = $this->combinationRepository->findById(Uuid::fromString($clientRequest->combinationId));
        if ($combination === null) {
            throw new CombinationNotFoundException($combinationId->toString());
        }

        $this->combinationRepository->updateLastUsageTime($combination);
        $request = $request->withAttribute(Combination::class, $combination);
        return $handler->handle($request);
    }
}
