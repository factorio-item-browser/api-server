<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Entity\Mod as PortalMod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Entity\CombinationUpdate;
use FactorioItemBrowser\Api\Server\Service\CombinationUpdateService;
use FactorioItemBrowser\Api\Server\Service\ExportQueueService;
use FactorioItemBrowser\Api\Server\Service\ModPortalService;
use FactorioItemBrowser\Common\Constant\Constant;
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
     * The mocked combination repository.
     * @var CombinationRepository&MockObject
     */
    protected $combinationRepository;

    /**
     * The mocked export queue service.
     * @var ExportQueueService&MockObject
     */
    protected $exportQueueService;

    /**
     * The mocked mod portal service.
     * @var ModPortalService&MockObject
     */
    protected $modPortalService;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->combinationRepository = $this->createMock(CombinationRepository::class);
        $this->exportQueueService = $this->createMock(ExportQueueService::class);
        $this->modPortalService = $this->createMock(ModPortalService::class);
    }

    /**
     * @param array<string>|string[] $methods
     * @param string $baseVersion
     * @return CombinationUpdateService&MockObject
     */
    protected function createService(array $methods = [], string $baseVersion = '1.2.3')
    {
        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(array_merge($methods, ['fetchBaseVersion']))
                        ->disableOriginalConstructor()
                        ->getMock();
        $service->expects($this->once())
                ->method('fetchBaseVersion')
                ->willReturn($baseVersion);

        $service->__construct($this->combinationRepository, $this->exportQueueService, $this->modPortalService);
        return $service;
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $baseVersion = 'abc';

        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['fetchBaseVersion'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $service->expects($this->once())
                ->method('fetchBaseVersion')
                ->willReturn($baseVersion);

        $service->__construct($this->combinationRepository, $this->exportQueueService, $this->modPortalService);

        $this->assertSame($this->combinationRepository, $this->extractProperty($service, 'combinationRepository'));
        $this->assertSame($this->exportQueueService, $this->extractProperty($service, 'exportQueueService'));
        $this->assertSame($this->modPortalService, $this->extractProperty($service, 'modPortalService'));
        $this->assertSame($baseVersion, $this->extractProperty($service, 'baseVersion'));
    }

    /**
     * Tests the fetchBaseVersion method.
     * @throws ReflectionException
     * @covers ::fetchBaseVersion
     */
    public function testFetchBaseVersion(): void
    {
        $baseVersion = '1.2.3';

        $baseMod = new DatabaseMod();
        $baseMod->setName('base')
                ->setVersion($baseVersion);

        $baseCombination = new Combination();
        $baseCombination->getMods()->add($baseMod);

        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo(Uuid::fromString(Config::DEFAULT_COMBINATION_ID)))
                                    ->willReturn($baseCombination);

        /* @var CombinationUpdateService&MockObject $service */
        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['__construct'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->injectProperty($service, 'combinationRepository', $this->combinationRepository);

        $result = $this->invokeMethod($service, 'fetchBaseVersion');

        $this->assertSame($baseVersion, $result);
    }

    /**
     * Tests the fetchBaseVersion method.
     * @throws ReflectionException
     * @covers ::fetchBaseVersion
     */
    public function testFetchBaseVersionWithoutCombination(): void
    {
        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo(Uuid::fromString(Config::DEFAULT_COMBINATION_ID)))
                                    ->willReturn(null);

        /* @var CombinationUpdateService&MockObject $service */
        $service = $this->getMockBuilder(CombinationUpdateService::class)
                        ->onlyMethods(['__construct'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->injectProperty($service, 'combinationRepository', $this->combinationRepository);

        $result = $this->invokeMethod($service, 'fetchBaseVersion');

        $this->assertSame('', $result);
    }
    
    /**
     * Tests the checkCombination method.
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombination(): void
    {
        $baseVersion = '4.2';
        
        $baseMod = new DatabaseMod();
        $baseMod->setName(Constant::MOD_NAME_BASE)
                ->setVersion('2.1');
        
        $databaseMod1 = new DatabaseMod();
        $databaseMod1->setName('abc')
                     ->setVersion('1.2.3');

        $databaseMod2 = new DatabaseMod();
        $databaseMod2->setName('def')
                     ->setVersion('2.3.4');
        
        $databaseMod3 = new DatabaseMod();
        $databaseMod3->setName('ghi')
                     ->setVersion('3.4.5');
        
        $combination = new Combination();
        $combination->getMods()->add($baseMod);
        $combination->getMods()->add($databaseMod1);
        $combination->getMods()->add($databaseMod2);
        $combination->getMods()->add($databaseMod3);
        
        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastUsage = 42;
        $combinationUpdate->secondsSinceLastImport = 1337;
        
        $portalMod1 = $this->createMock(PortalMod::class);
        $portalMod2 = $this->createMock(PortalMod::class);
        $portalMod3 = $this->createMock(PortalMod::class);
        
        $portalMods = [
            'abc' => $portalMod1,
            'def' => $portalMod2,
            'ghi' => $portalMod3,
        ];
        
        $release1 = new Release();
        $release1->setVersion('1.2.4');
        $release2 = new Release();
        $release2->setVersion('2.3.4');
        $release3 = new Release();
        $release3->setVersion('4.5.6');
        
        $this->modPortalService->expects($this->once())
                               ->method('requestModsOfCombination')
                               ->with($this->identicalTo($combination))
                               ->willReturn($portalMods);
        
        $service = $this->createService(['createCombinationUpdate', 'selectLatestRelease'], $baseVersion);
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);
        $service->expects($this->exactly(3))
                ->method('selectLatestRelease')
                ->withConsecutive(
                    [$this->identicalTo($portalMod1)],
                    [$this->identicalTo($portalMod2)],
                    [$this->identicalTo($portalMod3)],
                )
                ->willReturnOnConsecutiveCalls(
                    $release1,
                    $release2,
                    $release3,
                );
        
        $result = $service->checkCombination($combination);
        
        $this->assertSame($combinationUpdate, $result);
        $this->assertSame(2, $result->numberOfModUpdates);
        $this->assertTrue($result->hasBaseModUpdate);
    }

    /**
     * Tests the checkCombination method.
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombinationWithUnusedCombination(): void
    {
        $baseVersion = '4.2';

        $combination = new Combination();

        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastUsage = 42;
        $combinationUpdate->secondsSinceLastImport = 0;

        $this->modPortalService->expects($this->never())
                               ->method('requestModsOfCombination');

        $service = $this->createService(['createCombinationUpdate', 'selectLatestRelease'], $baseVersion);
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);
        $service->expects($this->never())
                ->method('selectLatestRelease');

        $result = $service->checkCombination($combination);

        $this->assertNull($result);
    }

    /**
     * Tests the checkCombination method.
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombinationWithMissingPortalMod(): void
    {
        $baseVersion = '4.2';

        $databaseMod1 = new DatabaseMod();
        $databaseMod1->setName('abc')
                     ->setVersion('1.2.3');

        $databaseMod2 = new DatabaseMod();
        $databaseMod2->setName('def')
                     ->setVersion('2.3.4');

        $combination = new Combination();
        $combination->getMods()->add($databaseMod1);
        $combination->getMods()->add($databaseMod2);

        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastUsage = 42;
        $combinationUpdate->secondsSinceLastImport = 1337;

        $portalMod1 = $this->createMock(PortalMod::class);

        $portalMods = [
            'abc' => $portalMod1,
        ];

        $release1 = new Release();
        $release1->setVersion('1.2.4');

        $this->modPortalService->expects($this->once())
                               ->method('requestModsOfCombination')
                               ->with($this->identicalTo($combination))
                               ->willReturn($portalMods);

        $service = $this->createService(['createCombinationUpdate', 'selectLatestRelease'], $baseVersion);
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);
        $service->expects($this->exactly(1))
                ->method('selectLatestRelease')
                ->withConsecutive(
                    [$this->identicalTo($portalMod1)],
                )
                ->willReturnOnConsecutiveCalls(
                    $release1,
                );

        $result = $service->checkCombination($combination);

        $this->assertNull($result);
    }

    /**
     * Tests the checkCombination method.
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombinationWithoutRelease(): void
    {
        $baseVersion = '4.2';

        $databaseMod1 = new DatabaseMod();
        $databaseMod1->setName('abc')
                     ->setVersion('1.2.3');

        $databaseMod2 = new DatabaseMod();
        $databaseMod2->setName('def')
                     ->setVersion('2.3.4');

        $combination = new Combination();
        $combination->getMods()->add($databaseMod1);
        $combination->getMods()->add($databaseMod2);

        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastUsage = 42;
        $combinationUpdate->secondsSinceLastImport = 1337;

        $portalMod1 = $this->createMock(PortalMod::class);
        $portalMod2 = $this->createMock(PortalMod::class);

        $portalMods = [
            'abc' => $portalMod1,
            'def' => $portalMod2,
        ];

        $release1 = new Release();
        $release1->setVersion('1.2.4');

        $this->modPortalService->expects($this->once())
                               ->method('requestModsOfCombination')
                               ->with($this->identicalTo($combination))
                               ->willReturn($portalMods);

        $service = $this->createService(['createCombinationUpdate', 'selectLatestRelease'], $baseVersion);
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);
        $service->expects($this->exactly(2))
                ->method('selectLatestRelease')
                ->withConsecutive(
                    [$this->identicalTo($portalMod1)],
                    [$this->identicalTo($portalMod2)],
                )
                ->willReturnOnConsecutiveCalls(
                    $release1,
                    null,
                );

        $result = $service->checkCombination($combination);

        $this->assertNull($result);
    }

    /**
     * Tests the checkCombination method.
     * @throws ClientException
     * @covers ::checkCombination
     */
    public function testCheckCombinationWithoutActualUpdate(): void
    {
        $baseVersion = '4.2';

        $databaseMod1 = new DatabaseMod();
        $databaseMod1->setName('abc')
                     ->setVersion('1.2.3');

        $databaseMod2 = new DatabaseMod();
        $databaseMod2->setName('def')
                     ->setVersion('2.3.4');

        $combination = new Combination();
        $combination->getMods()->add($databaseMod1);
        $combination->getMods()->add($databaseMod2);

        $combinationUpdate = new CombinationUpdate();
        $combinationUpdate->secondsSinceLastUsage = 42;
        $combinationUpdate->secondsSinceLastImport = 1337;

        $portalMod1 = $this->createMock(PortalMod::class);
        $portalMod2 = $this->createMock(PortalMod::class);

        $portalMods = [
            'abc' => $portalMod1,
            'def' => $portalMod2,
        ];

        $release1 = new Release();
        $release1->setVersion('1.2.3');
        $release2 = new Release();
        $release2->setVersion('2.3.4');

        $this->modPortalService->expects($this->once())
                               ->method('requestModsOfCombination')
                               ->with($this->identicalTo($combination))
                               ->willReturn($portalMods);

        $service = $this->createService(['createCombinationUpdate', 'selectLatestRelease'], $baseVersion);
        $service->expects($this->once())
                ->method('createCombinationUpdate')
                ->with($this->identicalTo($combination))
                ->willReturn($combinationUpdate);
        $service->expects($this->exactly(2))
                ->method('selectLatestRelease')
                ->withConsecutive(
                    [$this->identicalTo($portalMod1)],
                    [$this->identicalTo($portalMod2)],
                )
                ->willReturnOnConsecutiveCalls(
                    $release1,
                    $release2,
                );

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

        $service = $this->createService();

        /* @var CombinationUpdate $result */
        $result = $this->invokeMethod($service, 'createCombinationUpdate', $combination);

        $this->assertSame($combination, $result->combination);
        $this->assertGreaterThan(0, $result->secondsSinceLastImport);
        $this->assertGreaterThan(0, $result->secondsSinceLastUsage);
    }

    /**
     * Tests the selectLatestRelease method.
     * @throws ReflectionException
     * @covers ::selectLatestRelease
     */
    public function testSelectLatestRelease(): void
    {
        $baseVersion = '4.2.0';

        $release1 = new Release();
        $release1->setVersion('1.2.3');
        $release1->getInfoJson()->setFactorioVersion('4.2');

        $release2 = new Release();
        $release2->setVersion('9.9.9');
        $release2->getInfoJson()->setFactorioVersion('2.1');

        $release3 = new Release();
        $release3->setVersion('2.3.4');
        $release3->getInfoJson()->setFactorioVersion('4.2');

        $release4 = new Release();
        $release4->setVersion('0.1.2');
        $release4->getInfoJson()->setFactorioVersion('4.2');

        $mod = new PortalMod();
        $mod->setReleases([$release1, $release2, $release3, $release4]);

        $expectedResult = $release3;

        $service = $this->createService([], $baseVersion);
        $result = $this->invokeMethod($service, 'selectLatestRelease', $mod);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the selectLatestRelease method.
     * @throws ReflectionException
     * @covers ::selectLatestRelease
     */
    public function testSelectLatestReleaseWithoutMatch(): void
    {
        $baseVersion = '4.2.0';

        $release1 = new Release();
        $release1->setVersion('9.9.9');
        $release1->getInfoJson()->setFactorioVersion('2.1');

        $mod = new PortalMod();
        $mod->setReleases([$release1]);

        $service = $this->createService([], $baseVersion);
        $result = $this->invokeMethod($service, 'selectLatestRelease', $mod);

        $this->assertNull($result);
    }

    /**
     * Provides the data for the compareVersions test.
     * @return array<mixed>
     */
    public function provideCompareVersions(): array
    {
        return [
            ['1.2.3', '2.3.4', 3, -1],
            ['2.3.4', '1.2.3', 3, 1],
            ['1.2.3', '1.2.4', 3, -1],
            ['1.2.3', '1.2.2', 3, 1],
            ['1.2.3', '1.2.4', 2, 0],
        ];
    }

    /**
     * Tests the compareVersions method.
     * @param string $leftVersion
     * @param string $rightVersion
     * @param int $numberOfParts
     * @param int $expectedResult
     * @throws ReflectionException
     * @covers ::compareVersions
     * @dataProvider provideCompareVersions
     */
    public function testCompareVersions(
        string $leftVersion,
        string $rightVersion,
        int $numberOfParts,
        int $expectedResult
    ): void {
        $service = $this->createService();
        $result = $this->invokeMethod($service, 'compareVersions', $leftVersion, $rightVersion, $numberOfParts);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the splitVersion test.
     * @return array<mixed>
     */
    public function provideSplitVersion(): array
    {
        return [
            ['1.2.3', 3, [1, 2, 3]],
            ['1.2.3', 2, [1, 2]],
            ['1.2.3', 4, [1, 2, 3, 0]],

            ['', 3, [0, 0, 0]],
        ];
    }

    /**
     * Tests the splitVersion method.
     * @param string $version
     * @param int $numberOfParts
     * @param array<int> $expectedResult
     * @throws ReflectionException
     * @covers ::splitVersion
     * @dataProvider provideSplitVersion
     */
    public function testSplitVersion(string $version, int $numberOfParts, array $expectedResult): void
    {
        $service = $this->createService();
        $result = $this->invokeMethod($service, 'splitVersion', $version, $numberOfParts);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the requestExportStatus method.
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

        $service = $this->createService();
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

        $service = $this->createService();
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

        $service = $this->createService(['calculateScore']);
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

        $service = $this->createService();
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

        $service = $this->createService();
        $service->triggerExports($combinationUpdates);
    }
}
