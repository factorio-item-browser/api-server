<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Combination;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationExportRequest;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\ActionNotAllowedException;
use FactorioItemBrowser\Api\Server\Handler\Combination\CombinationExportHandler;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\ExportQueueService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\ListRequest;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\DetailsResponse;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\ListResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationExportHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Combination\CombinationExportHandler
 */
class CombinationExportHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked agent service.
     * @var AgentService&MockObject
     */
    protected $agentService;

    /**
     * The mocked export queue service.
     * @var ExportQueueService&MockObject
     */
    protected $exportQueueService;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->agentService = $this->createMock(AgentService::class);
        $this->exportQueueService = $this->createMock(ExportQueueService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new CombinationExportHandler($this->agentService, $this->exportQueueService);

        $this->assertSame($this->agentService, $this->extractProperty($handler, 'agentService'));
        $this->assertSame($this->exportQueueService, $this->extractProperty($handler, 'exportQueueService'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = CombinationExportRequest::class;

        $handler = new CombinationExportHandler($this->agentService, $this->exportQueueService);
        $result = $this->invokeMethod($handler, 'getExpectedRequestClass');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the handleRequest method.
     * @throws ReflectionException
     * @covers ::handleRequest
     */
    public function testHandleRequest(): void
    {
        $combinationIdString = '79d41bb3-a6b8-4264-b88f-3308db993348';
        $combinationId = Uuid::fromString($combinationIdString);
        $modNames = ['abc', 'def'];

        /* @var ListRequest&MockObject $listRequest1 */
        $listRequest1 = $this->createMock(ListRequest::class);
        /* @var ListRequest&MockObject $listRequest2 */
        $listRequest2 = $this->createMock(ListRequest::class);
        /* @var ListResponse&MockObject $listResponse1 */
        $listResponse1 = $this->createMock(ListResponse::class);
        /* @var ListResponse&MockObject $listResponse2 */
        $listResponse2 = $this->createMock(ListResponse::class);
        /* @var DetailsResponse&MockObject $detailsResponse */
        $detailsResponse = $this->createMock(DetailsResponse::class);
        /* @var ExportJob&MockObject $exportJob1 */
        $exportJob1 = $this->createMock(ExportJob::class);
        /* @var ExportJob&MockObject $exportJob2 */
        $exportJob2 = $this->createMock(ExportJob::class);
        /* @var ExportJob&MockObject $newExportJob */
        $newExportJob = $this->createMock(ExportJob::class);
        /* @var CombinationStatusRequest&MockObject $clientRequest */
        $clientRequest = $this->createMock(CombinationStatusRequest::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->any())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);
        $authorizationToken->expects($this->once())
                           ->method('getModNames')
                           ->willReturn($modNames);

        $expectedResult = new CombinationStatusResponse();
        $expectedResult->setId($combinationIdString)
                       ->setModNames($modNames)
                       ->setLatestExportJob($newExportJob)
                       ->setLatestSuccessfulExportJob($exportJob2);

        $this->exportQueueService->expects($this->exactly(2))
                                 ->method('createListRequest')
                                 ->withConsecutive(
                                     [$this->identicalTo($combinationId), $this->identicalTo('')],
                                     [$this->identicalTo($combinationId), $this->identicalTo(JobStatus::DONE)]
                                 )
                                 ->willReturnOnConsecutiveCalls(
                                     $listRequest1,
                                     $listRequest2
                                 );
        $this->exportQueueService->expects($this->once())
                                 ->method('executeListRequests')
                                 ->with($this->identicalTo([$listRequest1, $listRequest2]))
                                 ->willReturn([$listResponse1, $listResponse2]);
        $this->exportQueueService->expects($this->exactly(3))
                                 ->method('mapResponseToExportJob')
                                 ->withConsecutive(
                                     [$this->identicalTo($listResponse1)],
                                     [$this->identicalTo($listResponse2)],
                                     [$this->identicalTo($detailsResponse)]
                                 )
                                 ->willReturnOnConsecutiveCalls(
                                     $exportJob1,
                                     $exportJob2,
                                     $newExportJob
                                 );
        $this->exportQueueService->expects($this->once())
                                 ->method('createExportForAuthorizationToken')
                                 ->with($this->identicalTo($authorizationToken))
                                 ->willReturn($detailsResponse);

        /* @var CombinationExportHandler&MockObject $handler */
        $handler = $this->getMockBuilder(CombinationExportHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'isAgentAllowed', 'isNewExportRequired'])
                        ->setConstructorArgs([$this->agentService, $this->exportQueueService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('isAgentAllowed')
                ->with($this->identicalTo($authorizationToken))
                ->willReturn(true);
        $handler->expects($this->once())
                ->method('isNewExportRequired')
                ->with($this->identicalTo($exportJob1))
                ->willReturn(true);

        $result = $this->invokeMethod($handler, 'handleRequest', $clientRequest);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the handleRequest method.
     * @throws ReflectionException
     * @covers ::handleRequest
     */
    public function testHandleRequestWithoutNewExport(): void
    {
        $combinationIdString = '79d41bb3-a6b8-4264-b88f-3308db993348';
        $combinationId = Uuid::fromString($combinationIdString);
        $modNames = ['abc', 'def'];

        /* @var ListRequest&MockObject $listRequest1 */
        $listRequest1 = $this->createMock(ListRequest::class);
        /* @var ListRequest&MockObject $listRequest2 */
        $listRequest2 = $this->createMock(ListRequest::class);
        /* @var ListResponse&MockObject $listResponse1 */
        $listResponse1 = $this->createMock(ListResponse::class);
        /* @var ListResponse&MockObject $listResponse2 */
        $listResponse2 = $this->createMock(ListResponse::class);
        /* @var ExportJob&MockObject $exportJob1 */
        $exportJob1 = $this->createMock(ExportJob::class);
        /* @var ExportJob&MockObject $exportJob2 */
        $exportJob2 = $this->createMock(ExportJob::class);
        /* @var CombinationStatusRequest&MockObject $clientRequest */
        $clientRequest = $this->createMock(CombinationStatusRequest::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->any())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);
        $authorizationToken->expects($this->once())
                           ->method('getModNames')
                           ->willReturn($modNames);

        $expectedResult = new CombinationStatusResponse();
        $expectedResult->setId($combinationIdString)
                       ->setModNames($modNames)
                       ->setLatestExportJob($exportJob1)
                       ->setLatestSuccessfulExportJob($exportJob2);

        $this->exportQueueService->expects($this->exactly(2))
                                 ->method('createListRequest')
                                 ->withConsecutive(
                                     [$this->identicalTo($combinationId), $this->identicalTo('')],
                                     [$this->identicalTo($combinationId), $this->identicalTo(JobStatus::DONE)]
                                 )
                                 ->willReturnOnConsecutiveCalls(
                                     $listRequest1,
                                     $listRequest2
                                 );
        $this->exportQueueService->expects($this->once())
                                 ->method('executeListRequests')
                                 ->with($this->identicalTo([$listRequest1, $listRequest2]))
                                 ->willReturn([$listResponse1, $listResponse2]);
        $this->exportQueueService->expects($this->exactly(2))
                                 ->method('mapResponseToExportJob')
                                 ->withConsecutive(
                                     [$this->identicalTo($listResponse1)],
                                     [$this->identicalTo($listResponse2)],
                                 )
                                 ->willReturnOnConsecutiveCalls(
                                     $exportJob1,
                                     $exportJob2
                                 );
        $this->exportQueueService->expects($this->never())
                                 ->method('createExportForAuthorizationToken');

        /* @var CombinationExportHandler&MockObject $handler */
        $handler = $this->getMockBuilder(CombinationExportHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'isAgentAllowed', 'isNewExportRequired'])
                        ->setConstructorArgs([$this->agentService, $this->exportQueueService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('isAgentAllowed')
                ->with($this->identicalTo($authorizationToken))
                ->willReturn(true);
        $handler->expects($this->once())
                ->method('isNewExportRequired')
                ->with($this->identicalTo($exportJob1))
                ->willReturn(false);

        $result = $this->invokeMethod($handler, 'handleRequest', $clientRequest);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the handleRequest method.
     * @throws ReflectionException
     * @covers ::handleRequest
     */
    public function testHandleRequestWithDemoAgent(): void
    {
        /* @var CombinationStatusRequest&MockObject $clientRequest */
        $clientRequest = $this->createMock(CombinationStatusRequest::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->never())
                           ->method('getCombinationId');
        $authorizationToken->expects($this->never())
                           ->method('getModNames');

        $this->exportQueueService->expects($this->never())
                                 ->method('createListRequest');
        $this->exportQueueService->expects($this->never())
                                 ->method('executeListRequests');
        $this->exportQueueService->expects($this->never())
                                 ->method('mapResponseToExportJob');
        $this->exportQueueService->expects($this->never())
                                 ->method('createExportForAuthorizationToken');

        $this->expectException(ActionNotAllowedException::class);

        /* @var CombinationExportHandler&MockObject $handler */
        $handler = $this->getMockBuilder(CombinationExportHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'isAgentAllowed', 'isNewExportRequired'])
                        ->setConstructorArgs([$this->agentService, $this->exportQueueService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('isAgentAllowed')
                ->with($this->identicalTo($authorizationToken))
                ->willReturn(false);
        $handler->expects($this->never())
                ->method('isNewExportRequired');

        $this->invokeMethod($handler, 'handleRequest', $clientRequest);
    }

    /**
     * Provides the data for the isAgentAllowed test.
     * @return array<mixed>
     */
    public function provideIsAgentAllowed(): array
    {
        $agent1 = new Agent();
        $agent1->setIsDemo(false);

        $agent2 = new Agent();
        $agent2->setIsDemo(true);

        return [
            [$agent1, true],
            [$agent2, false],
            [null, false],
        ];
    }

    /**
     * Tests the isAgentAllowed method.
     * @param Agent|null $agent
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isAgentAllowed
     * @dataProvider provideIsAgentAllowed
     */
    public function testIsAgentAllowed(?Agent $agent, bool $expectedResult): void
    {
        $name = 'abc';

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getAgentName')
                           ->willReturn($name);

        $this->agentService->expects($this->once())
                           ->method('getByName')
                           ->with($this->identicalTo($name))
                           ->willReturn($agent);

        $handler = new CombinationExportHandler($this->agentService, $this->exportQueueService);
        $result = $this->invokeMethod($handler, 'isAgentAllowed', $authorizationToken);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the isNewExportRequired test.
     * @return array<mixed>
     */
    public function provideIsNewExportRequired(): array
    {
        return [
            [(new ExportJob())->setStatus(JobStatus::QUEUED), false],
            [(new ExportJob())->setStatus(JobStatus::DOWNLOADING), false],
            [(new ExportJob())->setStatus(JobStatus::PROCESSING), false],
            [(new ExportJob())->setStatus(JobStatus::UPLOADING), false],
            [(new ExportJob())->setStatus(JobStatus::UPLOADED), false],
            [(new ExportJob())->setStatus(JobStatus::IMPORTING), false],

            [(new ExportJob())->setStatus(JobStatus::DONE), true],
            [(new ExportJob())->setStatus(JobStatus::ERROR), true],
            [null, true],
        ];
    }

    /**
     * Tests the isNewExportRequired method.
     * @param ExportJob|null $exportJob
     * @param bool $expectedResult
     * @throws ReflectionException
     * @covers ::isNewExportRequired
     * @dataProvider provideIsNewExportRequired
     */
    public function testIsNewExportRequired(?ExportJob $exportJob, bool $expectedResult): void
    {
        $handler = new CombinationExportHandler($this->agentService, $this->exportQueueService);
        $result = $this->invokeMethod($handler, 'isNewExportRequired', $exportJob);

        $this->assertSame($expectedResult, $result);
    }
}
