<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Client\Client;
use BluePsyduck\FactorioModPortalClient\Entity\Mod as PortalMod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\FullModRequest;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Service\ModPortalService;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
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
     * The mocked mod portal client.
     * @var Client&MockObject
     */
    protected $modPortalClient;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modPortalClient = $this->createMock(Client::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new ModPortalService($this->modPortalClient);

        $this->assertSame($this->modPortalClient, $this->extractProperty($service, 'modPortalClient'));
        $this->assertSame([], $this->extractProperty($service, 'requestedMods'));
    }

    /**
     * Tests the getMods method.
     * @throws ReflectionException
     * @throws ClientException
     * @covers ::getMods
     */
    public function testGetMods(): void
    {
        $modNames = ['abc', 'def', 'ghi'];
        $missingModNames = ['ghi'];

        $mod1 = $this->createMock(PortalMod::class);
        $mod2 = $this->createMock(PortalMod::class);

        $requestedMods = [
            'abc' => $mod1,
            'def' => null,
            'ghi' => $mod2,
        ];
        $expectedResult = [
            'abc' => $mod1,
            'ghi' => $mod2,
        ];

        $service = $this->getMockBuilder(ModPortalService::class)
                        ->onlyMethods(['selectMissingModNames', 'requestMods'])
                        ->setConstructorArgs([$this->modPortalClient])
                        ->getMock();
        $service->expects($this->once())
                ->method('selectMissingModNames')
                ->with($this->identicalTo($modNames))
                ->willReturn($missingModNames);
        $service->expects($this->once())
                ->method('requestMods')
                ->with($this->identicalTo($missingModNames));
        $this->injectProperty($service, 'requestedMods', $requestedMods);

        $result = $service->getMods($modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getModsOfCombination method.
     * @throws ClientException
     * @covers ::getModsOfCombination
     */
    public function testGetModsOfCombination(): void
    {
        $mod1 = new DatabaseMod();
        $mod1->setName('abc');
        $mod2 = new DatabaseMod();
        $mod2->setName('def');

        $combination = new Combination();
        $combination->getMods()->add($mod1);
        $combination->getMods()->add($mod2);

        $expectedModNames = ['abc', 'def'];
        $mods = [
            'abc' => $this->createMock(PortalMod::class),
            'def' => $this->createMock(PortalMod::class),
        ];

        $service = $this->getMockBuilder(ModPortalService::class)
                        ->onlyMethods(['getMods'])
                        ->setConstructorArgs([$this->modPortalClient])
                        ->getMock();
        $service->expects($this->once())
                ->method('getMods')
                ->with($this->identicalTo($expectedModNames))
                ->willReturn($mods);

        $result = $service->getModsOfCombination($combination);

        $this->assertSame($mods, $result);
    }

    /**
     * Tests the selectMissingModNames method.
     * @throws ReflectionException
     * @covers ::selectMissingModNames
     */
    public function testSelectMissingModNames(): void
    {
        $modNames = ['abc', 'def', 'ghi', 'jkl'];
        $requestedMods = [
            'def' => $this->createMock(PortalMod::class),
            'jkl' => null,
        ];
        $expectedResult = ['abc', 'ghi'];

        $service = new ModPortalService($this->modPortalClient);
        $this->injectProperty($service, 'requestedMods', $requestedMods);

        $result = $this->invokeMethod($service, 'selectMissingModNames', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the requestMods method.
     * @throws ReflectionException
     * @covers ::requestMods
     */
    public function testRequestMods(): void
    {
        $modNames = ['abc', 'def', 'ghi'];

        $mod0 = $this->createMock(PortalMod::class);
        $mod1 = $this->createMock(PortalMod::class);
        $mod2 = $this->createMock(PortalMod::class);

        $request1 = new FullModRequest();
        $request1->setName('abc');
        $request2 = new FullModRequest();
        $request2->setName('def');
        $request3 = new FullModRequest();
        $request3->setName('ghi');

        $promise1 = new FulfilledPromise($mod1);
        $promise2 = new RejectedPromise('fail');
        $promise3 = new FulfilledPromise($mod2);

        $requestedMods = [
            'foo' => $mod0,
        ];
        $expectedRequestedMods = [
            'foo' => $mod0,
            'abc' => $mod1,
            'def' => null,
            'ghi' => $mod2,
        ];

        $this->modPortalClient->expects($this->exactly(3))
                              ->method('sendRequest')
                              ->withConsecutive(
                                  [$this->equalTo($request1)],
                                  [$this->equalTo($request2)],
                                  [$this->equalTo($request3)],
                              )
                              ->willReturnOnConsecutiveCalls(
                                  $promise1,
                                  $promise2,
                                  $promise3,
                              );

        $service = new ModPortalService($this->modPortalClient);
        $this->injectProperty($service, 'requestedMods', $requestedMods);

        $this->invokeMethod($service, 'requestMods', $modNames);

        $this->assertEquals($expectedRequestedMods, $this->extractProperty($service, 'requestedMods'));
    }

    /**
     * Tests the getLatestReleases method.
     * @throws ClientException
     * @covers ::getLatestReleases
     */
    public function testGetLatestReleases(): void
    {
        $modNames = ['abc', 'def'];
        $baseVersion = '1.2.3';

        $release1 = new Release();
        $release1->setFileName('abc');
        $release1->getInfoJson()->setFactorioVersion(new Version('1.2.3'));
        $mod1 = new PortalMod();
        $mod1->setName('abc')
             ->setReleases([$release1]);

        $release2 = new Release();
        $release2->setFileName('def');
        $release2->getInfoJson()->setFactorioVersion(new Version('1.2.3'));
        $mod2 = new PortalMod();
        $mod2->setName('def')
             ->setReleases([$release2]);

        $mods = [
            'abc' => $mod1,
            'def' => $mod2,
        ];
        $expectedResult = [
            'abc' => $release1,
            'def' => $release2,
        ];

        $service = $this->getMockBuilder(ModPortalService::class)
                        ->onlyMethods(['getMods'])
                        ->setConstructorArgs([$this->modPortalClient])
                        ->getMock();
        $service->expects($this->once())
                ->method('getMods')
                ->with($this->identicalTo($modNames))
                ->willReturn($mods);

        $result = $service->getLatestReleases($modNames, $baseVersion);

        $this->assertEquals($expectedResult, $result);
    }
}
