<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Combination;

use BluePsyduck\MapperManager\Exception\MapperException;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationExportRequest;
use FactorioItemBrowser\Api\Client\Request\RequestInterface as ClientRequestInterface;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\ActionNotAllowedException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\ExportQueueService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException;

/**
 * The handler of the combination export request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationExportHandler extends AbstractRequestHandler
{
    /**
     * The agent service.
     * @var AgentService
     */
    protected $agentService;

    /**
     * The export queue service.
     * @var ExportQueueService
     */
    protected $exportQueueService;

    /**
     * Initializes the handler.
     * @param AgentService $agentService
     * @param ExportQueueService $exportQueueService
     */
    public function __construct(AgentService $agentService, ExportQueueService $exportQueueService)
    {
        $this->agentService = $agentService;
        $this->exportQueueService = $exportQueueService;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return CombinationExportRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param ClientRequestInterface $clientRequest
     * @return ClientResponseInterface
     * @throws ActionNotAllowedException
     * @throws ClientException
     * @throws MapperException
     */
    protected function handleRequest($clientRequest): ClientResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();
        if (!$this->isAgentAllowed($authorizationToken)) {
            throw new ActionNotAllowedException();
        }

        [$latestJobResponse, $latestSuccessfulJobResponse] = $this->exportQueueService->executeListRequests([
            $this->exportQueueService->createListRequest($authorizationToken->getCombinationId()),
            $this->exportQueueService->createListRequest($authorizationToken->getCombinationId(), JobStatus::DONE),
        ]);

        $latestExportJob = $this->exportQueueService->mapResponseToExportJob($latestJobResponse);
        $latestSuccessfulExportJob = $this->exportQueueService->mapResponseToExportJob($latestSuccessfulJobResponse);

        if ($this->isNewExportRequired($latestExportJob)) {
            $newJobResponse = $this->exportQueueService->createExportForAuthorizationToken($authorizationToken);
            $latestExportJob = $this->exportQueueService->mapResponseToExportJob($newJobResponse);
        }

        $response = new CombinationStatusResponse();
        $response->setId($authorizationToken->getCombinationId()->toString())
                 ->setModNames($authorizationToken->getModNames())
                 ->setLatestExportJob($latestExportJob)
                 ->setLatestSuccessfulExportJob($latestSuccessfulExportJob);
        return $response;
    }

    /**
     * Returns whether the current agent is actually allowed to create exports.
     * @param AuthorizationToken $authorizationToken
     * @return bool
     */
    protected function isAgentAllowed(AuthorizationToken $authorizationToken): bool
    {
        $agent = $this->agentService->getByName($authorizationToken->getAgentName());
        return $agent !== null && !$agent->getIsDemo();
    }

    /**
     * Returns whether a new export is required.
     * @param ExportJob|null $latestExportJob
     * @return bool
     */
    protected function isNewExportRequired(?ExportJob $latestExportJob): bool
    {
        return !$latestExportJob instanceof ExportJob
            || in_array($latestExportJob->getStatus(), [JobStatus::DONE, JobStatus::ERROR], true);
    }
}
