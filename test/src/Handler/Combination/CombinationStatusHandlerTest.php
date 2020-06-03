<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Combination;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Client\Request\Combination\CombinationStatusRequest;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Combination\CombinationStatusHandler;
use FactorioItemBrowser\Api\Server\Service\ExportQueueService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\ListRequest;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\ListResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationStatusHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Combination\CombinationStatusHandler
 */
class CombinationStatusHandlerTest extends TestCase
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
        $handler = new CombinationStatusHandler($this->exportQueueService);

        $this->assertSame($this->exportQueueService, $this->extractProperty($handler, 'exportQueueService'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = CombinationStatusRequest::class;

        $handler = new CombinationStatusHandler($this->exportQueueService);
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
                                     [$this->identicalTo($listResponse2)]
                                 )
                                 ->willReturnOnConsecutiveCalls(
                                     $exportJob1,
                                     $exportJob2
                                 );

        /* @var CombinationStatusHandler&MockObject $handler */
        $handler = $this->getMockBuilder(CombinationStatusHandler::class)
                        ->onlyMethods(['getAuthorizationToken'])
                        ->setConstructorArgs([$this->exportQueueService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);

        $result = $this->invokeMethod($handler, 'handleRequest', $clientRequest);

        $this->assertEquals($expectedResult, $result);
    }
}
