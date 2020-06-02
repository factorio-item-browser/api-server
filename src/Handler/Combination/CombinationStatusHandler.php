<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Combination;

use BluePsyduck\MapperManager\Exception\MapperException;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Request\RequestInterface as ClientRequestInterface;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\ExportQueueService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;

/**
 * The handler of the combination status request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationStatusHandler extends AbstractRequestHandler
{
    /**
     * The export queue service.
     * @var ExportQueueService
     */
    protected $exportQueueService;

    /**
     * Initializes the handler.
     * @param ExportQueueService $exportQueueService
     */
    public function __construct(ExportQueueService $exportQueueService)
    {
        $this->exportQueueService = $exportQueueService;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return CombinationStatusRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param ClientRequestInterface $clientRequest
     * @return ClientResponseInterface
     * @throws MapperException
     */
    protected function handleRequest($clientRequest): ClientResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();

        [$latestJobResponse, $latestSuccessfulJobResponse] = $this->exportQueueService->executeListRequests([
            $this->exportQueueService->createListRequest($authorizationToken->getCombinationId()),
            $this->exportQueueService->createListRequest($authorizationToken->getCombinationId(), JobStatus::DONE),
        ]);

        $latestExportJob = $this->exportQueueService->mapResponseToExportJob($latestJobResponse);
        $latestSuccessfulExportJob = $this->exportQueueService->mapResponseToExportJob($latestSuccessfulJobResponse);

        $response = new CombinationStatusResponse();
        $response->setId($authorizationToken->getCombinationId()->toString())
                 ->setModNames($authorizationToken->getModNames())
                 ->setLatestExportJob($latestExportJob)
                 ->setLatestSuccessfulExportJob($latestSuccessfulExportJob);
        return $response;
    }
}
