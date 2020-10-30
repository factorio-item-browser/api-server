<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Client\Entity\ValidatedMod;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Entity\CombinationUpdate;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Service\CombinationUpdateService;
use FactorioItemBrowser\Api\Server\Service\CombinationValidationService;
use FactorioItemBrowser\Api\Server\Service\ExportQueueService;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobPriority;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException as ExportQueueClientException;
use FactorioItemBrowser\ExportQueue\Client\Request\Job\ListRequest;
use FactorioItemBrowser\ExportQueue\Client\Response\Job\ListResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationUpdateService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\CombinationUpdateService
 */
class CombinationUpdateServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination validation service.
     * @var CombinationValidationService&MockObject
     */
    protected $combinationValidationService;
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

        $this->combinationValidationService = $this->createMock(CombinationValidationService::class);
        $this->exportQueueService = $this->createMock(ExportQueueService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new CombinationUpdateService(
            $this->combinationValidationService,
            $this->exportQueueService,
        );

        $this->assertSame(
            $this->combinationValidationService,
            $this->extractProperty($service, 'combinationValidationService'),
        );
        $this->assertSame($this->exportQueueService, $this->extractProperty($service, 'exportQueueService'));
    }

    /**
     * Tests the checkCombination method.
     * @throws ApiServerException
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombination(): void
    {
        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastImport = 42;
        $combinationUpdate->secondsSinceLastUsage = 21;

        $mod1 = new DatabaseMod();
        $mod1->setName('abc')
             ->setVersion('1.2.3');
        $mod2 = new DatabaseMod();
        $mod2->setName('def')
             ->setVersion('2.3.4');
        $mod3 = new DatabaseMod();
        $mod3->setName('ghi')
             ->setVersion('3.4.5');

        $combination = new Combination();
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);
        $combination->getMods()->add($mod3);

        $expectedModNames = ['abc', 'def', 'ghi'];

        $validatedMod1 = new ValidatedMod();
        $validatedMod1->setVersion('1.2.4');
        $validatedMod2 = new ValidatedMod();
        $validatedMod2->setVersion('2.3.4');
        $validatedMod3 = new ValidatedMod();
        $validatedMod3->setVersion('4.5.6');

        $validatedMods = [
            'abc' => $validatedMod1,
            'def' => $validatedMod2,
            'ghi' => $validatedMod3,
        ];

        $expectedResult = new CombinationUpdate();
        $expectedResult->secondsSinceLastImport = 42;
        $expectedResult->secondsSinceLastUsage = 21;
        $expectedResult->numberOfModUpdates = 2;
        $expectedResult->hasBaseModUpdate = false;

        $this->combinationValidationService->expects($this->once())
                                           ->method('validate')
                                           ->with($this->equalTo($expectedModNames))
                                           ->willReturn($validatedMods);
        $this->combinationValidationService->expects($this->once())
                                           ->method('areModsValid')
                                           ->with($this->identicalTo($validatedMods))
                                           ->willReturn(true);

        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['createCombinationUpdate'])
                        ->setConstructorArgs([
                            $this->combinationValidationService,
                            $this->exportQueueService,
                        ])
                        ->getMock();
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);

        $result = $service->checkCombination($combination);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the checkCombination method.
     * @throws ApiServerException
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombinationWithBaseUpdate(): void
    {
        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastImport = 42;
        $combinationUpdate->secondsSinceLastUsage = 21;

        $mod1 = new DatabaseMod();
        $mod1->setName('base')
             ->setVersion('1.2.3');
        $mod2 = new DatabaseMod();
        $mod2->setName('def')
             ->setVersion('2.3.4');
        $mod3 = new DatabaseMod();
        $mod3->setName('ghi')
             ->setVersion('3.4.5');

        $combination = new Combination();
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);
        $combination->getMods()->add($mod3);

        $expectedModNames = ['base', 'def', 'ghi'];

        $validatedMod1 = new ValidatedMod();
        $validatedMod1->setVersion('1.2.4');
        $validatedMod2 = new ValidatedMod();
        $validatedMod2->setVersion('2.3.4');
        $validatedMod3 = new ValidatedMod();
        $validatedMod3->setVersion('4.5.6');

        $validatedMods = [
            'base' => $validatedMod1,
            'def' => $validatedMod2,
            'ghi' => $validatedMod3,
        ];

        $expectedResult = new CombinationUpdate();
        $expectedResult->secondsSinceLastImport = 42;
        $expectedResult->secondsSinceLastUsage = 21;
        $expectedResult->numberOfModUpdates = 2;
        $expectedResult->hasBaseModUpdate = true;

        $this->combinationValidationService->expects($this->once())
                                           ->method('validate')
                                           ->with($this->equalTo($expectedModNames))
                                           ->willReturn($validatedMods);
        $this->combinationValidationService->expects($this->once())
                                           ->method('areModsValid')
                                           ->with($this->identicalTo($validatedMods))
                                           ->willReturn(true);

        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['createCombinationUpdate'])
                        ->setConstructorArgs([
                            $this->combinationValidationService,
                            $this->exportQueueService,
                        ])
                        ->getMock();
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);

        $result = $service->checkCombination($combination);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the checkCombination method.
     * @throws ApiServerException
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombinationWithRecentImport(): void
    {
        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastImport = 21;
        $combinationUpdate->secondsSinceLastUsage = 42;

        $combination = $this->createMock(Combination::class);

        $this->combinationValidationService->expects($this->never())
                                           ->method('validate');
        $this->combinationValidationService->expects($this->never())
                                           ->method('areModsValid');

        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['createCombinationUpdate'])
                        ->setConstructorArgs([
                            $this->combinationValidationService,
                            $this->exportQueueService,
                        ])
                        ->getMock();
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);

        $result = $service->checkCombination($combination);

        $this->assertNull($result);
    }

    /**
     * Tests the checkCombination method.
     * @throws ApiServerException
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombinationWithInvalidMods(): void
    {
        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastImport = 42;
        $combinationUpdate->secondsSinceLastUsage = 21;

        $mod1 = new DatabaseMod();
        $mod1->setName('abc');
        $mod2 = new DatabaseMod();
        $mod2->setName('def');

        $combination = new Combination();
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);

        $expectedModNames = ['abc', 'def'];

        $validatedMods = [
            'abc' => $this->createMock(ValidatedMod::class),
            'def' => $this->createMock(ValidatedMod::class),
        ];

        $this->combinationValidationService->expects($this->once())
                                           ->method('validate')
                                           ->with($this->equalTo($expectedModNames))
                                           ->willReturn($validatedMods);
        $this->combinationValidationService->expects($this->once())
                                           ->method('areModsValid')
                                           ->with($this->identicalTo($validatedMods))
                                           ->willReturn(false);

        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['createCombinationUpdate'])
                        ->setConstructorArgs([
                            $this->combinationValidationService,
                            $this->exportQueueService,
                        ])
                        ->getMock();
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);

        $result = $service->checkCombination($combination);

        $this->assertNull($result);
    }

    /**
     * Tests the checkCombination method.
     * @throws ApiServerException
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombinationWithoutActualUpdate(): void
    {
        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastImport = 42;
        $combinationUpdate->secondsSinceLastUsage = 21;

        $mod1 = new DatabaseMod();
        $mod1->setName('abc')
             ->setVersion('1.2.3');
        $mod2 = new DatabaseMod();
        $mod2->setName('def')
             ->setVersion('2.3.4');

        $combination = new Combination();
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);

        $expectedModNames = ['abc', 'def'];

        $validatedMod1 = new ValidatedMod();
        $validatedMod1->setVersion('1.2.3');
        $validatedMod2 = new ValidatedMod();
        $validatedMod2->setVersion('2.3.4');

        $validatedMods = [
            'abc' => $validatedMod1,
            'def' => $validatedMod2,
        ];

        $this->combinationValidationService->expects($this->once())
                                           ->method('validate')
                                           ->with($this->equalTo($expectedModNames))
                                           ->willReturn($validatedMods);
        $this->combinationValidationService->expects($this->once())
                                           ->method('areModsValid')
                                           ->with($this->identicalTo($validatedMods))
                                           ->willReturn(true);

        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['createCombinationUpdate'])
                        ->setConstructorArgs([
                            $this->combinationValidationService,
                            $this->exportQueueService,
                        ])
                        ->getMock();
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);

        $result = $service->checkCombination($combination);

        $this->assertNull($result);
    }

    /**
     * Tests the createCombinationUpdate method.
     * @throws ReflectionException
     * @covers ::createCombinationUpdate
     */
    public function testCreateCombinationUpdate(): void
    {
        $combination = new Combination();
        $combination->setImportTime(new DateTime('-1 week'))
                    ->setLastUsageTime(new DateTime('-1 day'));

        $service = new CombinationUpdateService(
            $this->combinationValidationService,
            $this->exportQueueService,
        );

        /* @var CombinationUpdate $result */
        $result = $this->invokeMethod($service, 'createCombinationUpdate', $combination);

        $this->assertSame($combination, $result->combination);
        $this->assertGreaterThan(0, $result->secondsSinceLastImport);
        $this->assertGreaterThan(0, $result->secondsSinceLastUsage);
    }

    /**
     * Tests the requestExportStatus method.
     * @throws ExportQueueClientException
     * @throws MapperException
     * @covers ::requestExportStatus
     */
    public function testRequestExportStatus(): void
    {
        $combinationIdString1 = '07064f35-f843-4045-8240-944958ee6758';
        $combinationId1 = Uuid::fromString($combinationIdString1);
        $combination1 = new Combination();
        $combination1->setId($combinationId1);
        $combinationUpdate1 = new CombinationUpdate();
        $combinationUpdate1->combination = $combination1;
        $request1 = $this->createMock(ListRequest::class);
        $response1 = $this->createMock(ListResponse::class);
        $exportJob1 = new ExportJob();
        $exportJob1->setStatus('abc');

        $combinationIdString2 = 'f01eba1c-8572-4640-bd79-7ba3791d5919';
        $combinationId2 = Uuid::fromString($combinationIdString2);
        $combination2 = new Combination();
        $combination2->setId($combinationId2);
        $combinationUpdate2 = new CombinationUpdate();
        $combinationUpdate2->combination = $combination2;
        $request2 = $this->createMock(ListRequest::class);
        $response2 = $this->createMock(ListResponse::class);
        $exportJob2 = new ExportJob();
        $exportJob2->setStatus('def');

        $combinationUpdates = [$combinationUpdate1, $combinationUpdate2];
        $requests = [
            $combinationIdString1 => $request1,
            $combinationIdString2 => $request2,
        ];
        $responses = [
            $combinationIdString1 => $response1,
            $combinationIdString2 => $response2,
        ];

        $this->exportQueueService->expects($this->exactly(2))
                                 ->method('createListRequest')
                                 ->withConsecutive(
                                     [$this->identicalTo($combinationId1)],
                                     [$this->identicalTo($combinationId2)],
                                 )
                                 ->willReturnOnConsecutiveCalls(
                                     $request1,
                                     $request2,
                                 );
        $this->exportQueueService->expects($this->once())
                                 ->method('executeListRequests')
                                 ->with($this->identicalTo($requests))
                                 ->willReturn($responses);
        $this->exportQueueService->expects($this->exactly(2))
                                 ->method('mapResponseToExportJob')
                                 ->withConsecutive(
                                     [$this->identicalTo($response1)],
                                     [$this->identicalTo($response2)],
                                 )
                                 ->willReturnOnConsecutiveCalls(
                                     $exportJob1,
                                     $exportJob2
                                 );

        $service = new CombinationUpdateService(
            $this->combinationValidationService,
            $this->exportQueueService,
        );
        $service->requestExportStatus($combinationUpdates);

        $this->assertSame($combinationUpdate1->exportStatus, 'abc');
        $this->assertSame($combinationUpdate2->exportStatus, 'def');
    }

    /**
     * Tests the filter method.
     * @covers ::filter
     */
    public function testFilter(): void
    {
        $update1 = new CombinationUpdate();
        $update1->exportStatus = '';
        $update2 = new CombinationUpdate();
        $update2->exportStatus = JobStatus::DONE;
        $update3 = new CombinationUpdate();
        $update3->exportStatus = JobStatus::DOWNLOADING;
        $update4 = new CombinationUpdate();
        $update4->exportStatus = JobStatus::ERROR;
        $update5 = new CombinationUpdate();
        $update5->exportStatus = JobStatus::IMPORTING;
        $update6 = new CombinationUpdate();
        $update6->exportStatus = JobStatus::PROCESSING;
        $update7 = new CombinationUpdate();
        $update7->exportStatus = JobStatus::QUEUED;
        $update8 = new CombinationUpdate();
        $update8->exportStatus = JobStatus::UPLOADING;
        $update9 = new CombinationUpdate();
        $update9->exportStatus = JobStatus::UPLOADED;

        $updates = [$update1, $update2, $update3, $update4, $update5, $update6, $update7, $update8, $update9];
        $expectedResult = [$update1, $update2, $update4];

        $service = new CombinationUpdateService(
            $this->combinationValidationService,
            $this->exportQueueService,
        );
        $result = $service->filter($updates);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the sort method.
     * @covers ::sort
     */
    public function testSort(): void
    {
        $update1 = new CombinationUpdate();
        $update2 = new CombinationUpdate();
        $update3 = new CombinationUpdate();
        $update4 = new CombinationUpdate();

        $combinationUpdates = [$update1, $update2, $update3, $update4];
        $expectedResult = [$update3, $update1, $update2, $update4];

        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['calculateScore'])
                        ->setConstructorArgs([
                            $this->combinationValidationService,
                            $this->exportQueueService,
                        ])
                        ->getMock();
        $service->expects($this->exactly(4))
                ->method('calculateScore')
                ->withConsecutive(
                    [$this->identicalTo($update1)],
                    [$this->identicalTo($update2)],
                    [$this->identicalTo($update3)],
                    [$this->identicalTo($update4)],
                )
                ->willReturnOnConsecutiveCalls(
                    2,
                    2,
                    3,
                    1,
                );

        $result = $service->sort($combinationUpdates);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the calculateScore method.
     * @throws ReflectionException
     * @covers ::calculateScore
     */
    public function testCalculateScore(): void
    {
        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastImport = 5 * 86400;
        $combinationUpdate->secondsSinceLastUsage = 3 * 86400;
        $combinationUpdate->numberOfModUpdates = 7;
        $combinationUpdate->hasBaseModUpdate = false;
        $combinationUpdate->exportStatus = JobStatus::DONE;

        $expectedResult = 19;

        $service = new CombinationUpdateService(
            $this->combinationValidationService,
            $this->exportQueueService,
        );
        $result = $this->invokeMethod($service, 'calculateScore', $combinationUpdate);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the triggerExports method.
     * @throws ExportQueueClientException
     * @covers ::triggerExports
     */
    public function testTriggerExports(): void
    {
        $combination1 = $this->createMock(Combination::class);
        $combination2 = $this->createMock(Combination::class);

        $combinationUpdate1 = new CombinationUpdate();
        $combinationUpdate1->combination = $combination1;

        $combinationUpdate2 = new CombinationUpdate();
        $combinationUpdate2->combination = $combination2;

        $combinationUpdates = [$combinationUpdate1, $combinationUpdate2];

        $this->exportQueueService->expects($this->exactly(2))
                                 ->method('createExportForCombination')
                                 ->withConsecutive(
                                     [$this->identicalTo($combination1), $this->identicalTo(JobPriority::SCRIPT)],
                                     [$this->identicalTo($combination2), $this->identicalTo(JobPriority::SCRIPT)],
                                 );

        $service = new CombinationUpdateService(
            $this->combinationValidationService,
            $this->exportQueueService,
        );
        $service->triggerExports($combinationUpdates);
    }
}
