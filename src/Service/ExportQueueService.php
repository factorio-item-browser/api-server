<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\ExportQueue\Client\Client\Client;
use FactorioItemBrowser\ExportQueue\Client\Constant\ListOrder;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\CreateRequest;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\ListRequest;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\DetailsResponse;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\ListResponse;
use FactorioItemBrowser\ExportQueue\Client\Response\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Ramsey\Uuid\UuidInterface;

use function GuzzleHttp\Promise\all;

/**
 * The service handling requests to the export queue.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ExportQueueService
{
    /**
     * The client.
     * @var Client
     */
    protected $client;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * Initializes the service.
     * @param Client $client
     * @param MapperManagerInterface $mapperManager
     */
    public function __construct(Client $client, MapperManagerInterface $mapperManager)
    {
        $this->client = $client;
        $this->mapperManager = $mapperManager;
    }

    /**
     * Create a list request to the specified authorization token.
     * @param UuidInterface $combinationId
     * @param string $jobStatus
     * @return ListRequest
     */
    public function createListRequest(UuidInterface $combinationId, string $jobStatus = ''): ListRequest
    {
        $request = new ListRequest();
        $request->setCombinationId($combinationId->toString())
                ->setStatus($jobStatus)
                ->setOrder(ListOrder::LATEST)
                ->setLimit(1);
        return $request;
    }

    /**
     * Executes the specified list requests, waiting for all their responses.
     * @param array<ListRequest> $listRequests
     * @return array<ListResponse>
     */
    public function executeListRequests(array $listRequests): array
    {
        return all(array_map(function (ListRequest $request): PromiseInterface {
            return $this->client->sendRequest($request);
        }, $listRequests))->wait();
    }

    /**
     * Maps the response to an ExportJob instance, if possible.
     * @param ResponseInterface $response
     * @return ExportJob|null
     * @throws MapperException
     */
    public function mapResponseToExportJob(ResponseInterface $response): ?ExportJob
    {
        $job = null;
        if ($response instanceof ListResponse) {
            $jobs = $response->getJobs();
            $job = reset($jobs);
        } elseif ($response instanceof DetailsResponse) {
            $job = $response;
        }

        $exportJob = null;
        if ($job instanceof Job) {
            $exportJob = new ExportJob();
            $this->mapperManager->map($job, $exportJob);
        }
        return $exportJob;
    }

    /**
     * Creates an export to the authorization token in the export queue.
     * @param AuthorizationToken $authorizationToken
     * @return DetailsResponse
     * @throws ClientException
     */
    public function createExportForAuthorizationToken(AuthorizationToken $authorizationToken): DetailsResponse
    {
        $request = new CreateRequest();
        $request->setCombinationId($authorizationToken->getCombinationId()->toString())
                ->setModNames($authorizationToken->getModNames());

        return $this->client->sendRequest($request)->wait();
    }

    /**
     * Creates an export for the combination in the export queue.
     * @param Combination $combination
     * @param string $priority
     * @return DetailsResponse
     * @throws ClientException
     */
    public function createExportForCombination(Combination $combination, string $priority): DetailsResponse
    {
        $modNames = [];
        foreach ($combination->getMods() as $mod) {
            $modNames[] = $mod->getName();
        }

        $request = new CreateRequest();
        $request->setCombinationId($combination->getId()->toString())
                ->setModNames($modNames)
                ->setPriority($priority);

        return $this->client->sendRequest($request)->wait();
    }
}
