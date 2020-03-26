<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Service\ExportQueueService;
use FactorioItemBrowser\ExportQueue\Client\Client\Client;
use FactorioItemBrowser\ExportQueue\Client\Constant\ListOrder;
use FactorioItemBrowser\ExportQueue\Client\Entity\Job;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\CreateRequest;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\ListRequest;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\DetailsResponse;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\ListResponse;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the ExportQueueService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\ExportQueueService
 */
class ExportQueueServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked client.
     * @var Client&MockObject
     */
    protected $client;

    /**
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new ExportQueueService($this->client, $this->mapperManager);

        $this->assertSame($this->client, $this->extractProperty($service, 'client'));
        $this->assertSame($this->mapperManager, $this->extractProperty($service, 'mapperManager'));
    }

    /**
     * Tests the createListRequest method.
     * @covers ::createListRequest
     */
    public function testCreateListRequest(): void
    {
        $combinationId = '79d41bb3-a6b8-4264-b88f-3308db993348';
        $jobStatus = 'abc';

        $expectedResult = new ListRequest();
        $expectedResult->setCombinationId($combinationId)
                       ->setStatus($jobStatus)
                       ->setOrder(ListOrder::LATEST)
                       ->setLimit(1);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn(Uuid::fromString($combinationId));

        $service = new ExportQueueService($this->client, $this->mapperManager);
        $result = $service->createListRequest($authorizationToken, $jobStatus);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the executeListRequests method.
     * @covers ::executeListRequests
     */
    public function testExecuteListRequests(): void
    {
        /* @var ListRequest&MockObject $request1 */
        $request1 = $this->createMock(ListRequest::class);
        /* @var ListRequest&MockObject $request2 */
        $request2 = $this->createMock(ListRequest::class);
        $requests = [$request1, $request2];

        /* @var ListResponse&MockObject $response1 */
        $response1 = $this->createMock(ListResponse::class);
        /* @var ListResponse&MockObject $response2 */
        $response2 = $this->createMock(ListResponse::class);
        $expectedResult = [$response1, $response2];

        $promise1 = new FulfilledPromise($response1);
        $promise2 = new FulfilledPromise($response2);

        $this->client->expects($this->exactly(2))
                     ->method('sendRequest')
                     ->withConsecutive(
                         [$this->identicalTo($request1)],
                         [$this->identicalTo($request2)]
                     )
                     ->willReturnOnConsecutiveCalls(
                         $promise1,
                         $promise2
                     );

        $service = new ExportQueueService($this->client, $this->mapperManager);
        $result = $service->executeListRequests($requests);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the mapResponseToExportJob method.
     * @throws MapperException
     * @covers ::mapResponseToExportJob
     */
    public function testMapResponseToExportJobWithListResponse(): void
    {
        /* @var Job&MockObject $job */
        $job = $this->createMock(Job::class);

        /* @var ListResponse&MockObject $response */
        $response = $this->createMock(ListResponse::class);
        $response->expects($this->once())
                 ->method('getJobs')
                 ->willReturn([$job]);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($job), $this->isInstanceOf(ExportJob::class));

        $service = new ExportQueueService($this->client, $this->mapperManager);
        $result = $service->mapResponseToExportJob($response);

        $this->assertNotNull($result);
    }

    /**
     * Tests the mapResponseToExportJob method.
     * @throws MapperException
     * @covers ::mapResponseToExportJob
     */
    public function testMapResponseToExportJobWithEmptyListResponse(): void
    {

        /* @var ListResponse&MockObject $response */
        $response = $this->createMock(ListResponse::class);
        $response->expects($this->once())
                 ->method('getJobs')
                 ->willReturn([]);

        $this->mapperManager->expects($this->never())
                            ->method('map');

        $service = new ExportQueueService($this->client, $this->mapperManager);
        $result = $service->mapResponseToExportJob($response);

        $this->assertNull($result);
    }

    /**
     * Tests the mapResponseToExportJob method.
     * @throws MapperException
     * @covers ::mapResponseToExportJob
     */
    public function testMapResponseToExportJobWithDetailsResponse(): void
    {
        /* @var DetailsResponse&MockObject $response */
        $response = $this->createMock(DetailsResponse::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($response), $this->isInstanceOf(ExportJob::class));

        $service = new ExportQueueService($this->client, $this->mapperManager);
        $result = $service->mapResponseToExportJob($response);

        $this->assertNotNull($result);
    }

    /**
     * Tests the createExport method.
     * @throws ClientException
     * @covers ::createExport
     */
    public function testCreateExport(): void
    {
        $combinationId = '79d41bb3-a6b8-4264-b88f-3308db993348';
        $modNames = ['abc', 'def'];

        $expectedRequest = new CreateRequest();
        $expectedRequest->setCombinationId($combinationId)
                        ->setModNames($modNames);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn(Uuid::fromString($combinationId));
        $authorizationToken->expects($this->once())
                           ->method('getModNames')
                           ->willReturn($modNames);

        /* @var DetailsResponse&MockObject $response */
        $response = $this->createMock(DetailsResponse::class);

        $promise = new FulfilledPromise($response);

        $this->client->expects($this->once())
                     ->method('sendRequest')
                     ->with($this->equalTo($expectedRequest))
                     ->willReturn($promise);

        $service = new ExportQueueService($this->client, $this->mapperManager);
        $result = $service->createExport($authorizationToken);

        $this->assertSame($response, $result);
    }
}
