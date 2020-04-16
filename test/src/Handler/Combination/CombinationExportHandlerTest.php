<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Combination;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationExportRequest;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Combination\CombinationExportHandler;
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

        $this->exportQueueService = $this->createMock(ExportQueueService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new CombinationExportHandler($this->exportQueueService);

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

        $handler = new CombinationExportHandler($this->exportQueueService);
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
        $combinationId = '79d41bb3-a6b8-4264-b88f-3308db993348';
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
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn(Uuid::fromString($combinationId));
        $authorizationToken->expects($this->once())
                           ->method('getModNames')
                           ->willReturn($modNames);

        $expectedResult = new CombinationStatusResponse();
        $expectedResult->setId($combinationId)
                       ->setModNames($modNames)
                       ->setLatestExportJob($newExportJob)
                       ->setLatestSuccessfulExportJob($exportJob2);

        $this->exportQueueService->expects($this->exactly(2))
                                 ->method('createListRequest')
                                 ->withConsecutive(
                                     [$this->identicalTo($authorizationToken), $this->identicalTo('')],
                                     [$this->identicalTo($authorizationToken), $this->identicalTo(JobStatus::DONE)]
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
                                 ->method('createExport')
                                 ->with($this->identicalTo($authorizationToken))
                                 ->willReturn($detailsResponse);

        /* @var CombinationExportHandler&MockObject $handler */
        $handler = $this->getMockBuilder(CombinationExportHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'isNewExportRequired'])
                        ->setConstructorArgs([$this->exportQueueService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
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
        $combinationId = '79d41bb3-a6b8-4264-b88f-3308db993348';
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
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn(Uuid::fromString($combinationId));
        $authorizationToken->expects($this->once())
                           ->method('getModNames')
                           ->willReturn($modNames);

        $expectedResult = new CombinationStatusResponse();
        $expectedResult->setId($combinationId)
                       ->setModNames($modNames)
                       ->setLatestExportJob($exportJob1)
                       ->setLatestSuccessfulExportJob($exportJob2);

        $this->exportQueueService->expects($this->exactly(2))
                                 ->method('createListRequest')
                                 ->withConsecutive(
                                     [$this->identicalTo($authorizationToken), $this->identicalTo('')],
                                     [$this->identicalTo($authorizationToken), $this->identicalTo(JobStatus::DONE)]
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
                                 ->method('createExport');

        /* @var CombinationExportHandler&MockObject $handler */
        $handler = $this->getMockBuilder(CombinationExportHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'isNewExportRequired'])
                        ->setConstructorArgs([$this->exportQueueService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('isNewExportRequired')
                ->with($this->identicalTo($exportJob1))
                ->willReturn(false);

        $result = $this->invokeMethod($handler, 'handleRequest', $clientRequest);

        $this->assertEquals($expectedResult, $result);
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
        $handler = new CombinationExportHandler($this->exportQueueService);
        $result = $this->invokeMethod($handler, 'isNewExportRequired', $exportJob);

        $this->assertSame($expectedResult, $result);
    }
}
