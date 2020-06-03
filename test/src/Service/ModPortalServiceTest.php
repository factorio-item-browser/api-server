<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod as PortalMod;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\ModListRequest;
use BluePsyduck\FactorioModPortalClient\Response\ModListResponse;
use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Service\ModPortalService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModPortalService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\ModPortalService
 */
class ModPortalServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mod portal facade.
     * @var Facade&MockObject
     */
    protected $modPortalFacade;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modPortalFacade = $this->createMock(Facade::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new ModPortalService($this->modPortalFacade);

        $this->assertSame($this->modPortalFacade, $this->extractProperty($service, 'modPortalFacade'));
        $this->assertSame([], $this->extractProperty($service, 'requestedMods'));
    }

    /**
     * Tests the requestModsOfCombination method.
     * @throws ClientException
     * @covers ::requestModsOfCombination
     */
    public function testRequestModsOfCombination(): void
    {
        $databaseMod1 = new DatabaseMod();
        $databaseMod1->setName('abc');
        $databaseMod2 = new DatabaseMod();
        $databaseMod2->setName('def');

        $expectedModNames = ['abc', 'def'];
        $portalMods = [
            $this->createMock(PortalMod::class),
            $this->createMock(PortalMod::class),
        ];

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('getMods')
                    ->willReturn(new ArrayCollection([$databaseMod1, $databaseMod2]));

        /* @var ModPortalService&MockObject $service */
        $service = $this->getMockBuilder(ModPortalService::class)
                        ->onlyMethods(['requestMods'])
                        ->setConstructorArgs([$this->modPortalFacade])
                        ->getMock();
        $service->expects($this->once())
                ->method('requestMods')
                ->with($this->identicalTo($expectedModNames))
                ->willReturn($portalMods);

        $result = $service->requestModsOfCombination($combination);

        $this->assertSame($portalMods, $result);
    }

    /**
     * Tests the requestMods method.
     * @throws ClientException
     * @throws ReflectionException
     * @covers ::requestMods
     */
    public function testRequestMods(): void
    {
        $modNames = ['abc', 'def'];

        /* @var PortalMod&MockObject $portalMod1 */
        $portalMod1 = $this->createMock(PortalMod::class);
        /* @var PortalMod&MockObject $portalMod2 */
        $portalMod2 = $this->createMock(PortalMod::class);

        $requestedMods = [
            'abc' => $portalMod1,
            'def' => $portalMod2,
            'foo' => $this->createMock(PortalMod::class),
        ];
        $expectedResult = [
            'abc' => $portalMod1,
            'def' => $portalMod2,
        ];

        /* @var ModPortalService&MockObject $service */
        $service = $this->getMockBuilder(ModPortalService::class)
                        ->onlyMethods(['requestMissingMods'])
                        ->setConstructorArgs([$this->modPortalFacade])
                        ->getMock();
        $service->expects($this->once())
                ->method('requestMissingMods')
                ->with($this->identicalTo($modNames));
        $this->injectProperty($service, 'requestedMods', $requestedMods);

        $result = $service->requestMods($modNames);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the requestMissingMods method.
     * @throws ReflectionException
     * @covers ::requestMissingMods
     */
    public function testRequestMissingMods(): void
    {
        $modNames = ['abc', 'def', 'ghi'];

        /* @var PortalMod&MockObject $portalMod1 */
        $portalMod1 = $this->createMock(PortalMod::class);

        /* @var PortalMod&MockObject $portalMod2 */
        $portalMod2 = $this->createMock(PortalMod::class);
        $portalMod2->expects($this->once())
                   ->method('getName')
                   ->willReturn('def');

        /* @var PortalMod&MockObject $portalMod3 */
        $portalMod3 = $this->createMock(PortalMod::class);
        $portalMod3->expects($this->once())
                   ->method('getName')
                   ->willReturn('ghi');

        $expectedRequest = new ModListRequest();
        $expectedRequest->setNameList(['def', 'ghi'])
                        ->setPageSize(2);

        $requestedMods = [
            'abc' => $portalMod1,
        ];
        $expectedRequestedMods = [
            'abc' => $portalMod1,
            'def' => $portalMod2,
            'ghi' => $portalMod3,
        ];

        /* @var ModListResponse&MockObject $response */
        $response = $this->createMock(ModListResponse::class);
        $response->expects($this->once())
                 ->method('getResults')
                 ->willReturn([$portalMod2, $portalMod3]);

        $this->modPortalFacade->expects($this->once())
                              ->method('getModList')
                              ->with($this->equalTo($expectedRequest))
                              ->willReturn($response);

        $service = new ModPortalService($this->modPortalFacade);
        $this->injectProperty($service, 'requestedMods', $requestedMods);

        $this->invokeMethod($service, 'requestMissingMods', $modNames);

        $this->assertSame($expectedRequestedMods, $this->extractProperty($service, 'requestedMods'));
    }

    /**
     * Tests the requestMissingMods method.
     * @throws ReflectionException
     * @covers ::requestMissingMods
     */
    public function testRequestMissingModsWithoutMissingMods(): void
    {
        $modNames = ['abc'];

        /* @var PortalMod&MockObject $portalMod1 */
        $portalMod1 = $this->createMock(PortalMod::class);

        $requestedMods = [
            'abc' => $portalMod1,
        ];

        $this->modPortalFacade->expects($this->never())
                              ->method('getModList');

        $service = new ModPortalService($this->modPortalFacade);
        $this->injectProperty($service, 'requestedMods', $requestedMods);

        $this->invokeMethod($service, 'requestMissingMods', $modNames);

        $this->assertSame($requestedMods, $this->extractProperty($service, 'requestedMods'));
    }
}
