<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Entity\Dependency;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Constant\ValidatedModIssueType;
use FactorioItemBrowser\Api\Client\Entity\Mod as PortalMod;
use FactorioItemBrowser\Api\Client\Entity\ValidatedMod;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Exception\InternalServerException;
use FactorioItemBrowser\Api\Server\Service\CombinationService;
use FactorioItemBrowser\Api\Server\Service\CombinationValidationService;
use FactorioItemBrowser\Api\Server\Service\ModPortalService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationValidationService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\CombinationValidationService
 */
class CombinationValidationServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination service.
     * @var CombinationService&MockObject
     */
    protected $combinationService;

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

        $this->combinationService = $this->createMock(CombinationService::class);
        $this->modPortalService = $this->createMock(ModPortalService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new CombinationValidationService($this->combinationService, $this->modPortalService);

        $this->assertSame($this->combinationService, $this->extractProperty($service, 'combinationService'));
        $this->assertSame($this->modPortalService, $this->extractProperty($service, 'modPortalService'));
    }

    /**
     * Tests the validate method.
     * @throws ClientException
     * @throws InternalServerException
     * @covers ::validate
     */
    public function testValidate(): void
    {
        $modNames = ['base', 'abc', 'def'];

        $baseMod = new Mod();
        $baseMod->setVersion('1.2.3');
        $portalMod = $this->createMock(PortalMod::class);
        $portalMods = [
            'def' => $portalMod,
        ];
        $releases = [
            'def' => $this->createMock(Release::class),
        ];

        $validatedBaseMod = $this->createMock(ValidatedMod::class);
        $validatedMod1 = $this->createMock(ValidatedMod::class);
        $validatedMod2 = $this->createMock(ValidatedMod::class);

        $expectedResult = [
            'base' => $validatedBaseMod,
            'abc' => $validatedMod1,
            'def' => $validatedMod2,
        ];

        $this->combinationService->expects($this->once())
                                 ->method('getBaseMod')
                                 ->willReturn($baseMod);

        $this->modPortalService->expects($this->once())
                               ->method('getMods')
                               ->with($this->identicalTo($modNames))
                               ->willReturn($portalMods);
        $this->modPortalService->expects($this->once())
                               ->method('getLatestReleases')
                               ->with($this->identicalTo($modNames), $this->identicalTo('1.2.3'))
                               ->willReturn($releases);

        $service = $this->getMockBuilder(CombinationValidationService::class)
                        ->onlyMethods(['createValidatedBaseMod', 'createMissingMod', 'validateMod'])
                        ->setConstructorArgs([$this->combinationService, $this->modPortalService])
                        ->getMock();
        $service->expects($this->once())
                ->method('createValidatedBaseMod')
                ->with($this->identicalTo($baseMod))
                ->willReturn($validatedBaseMod);
        $service->expects($this->once())
                ->method('createMissingMod')
                ->with($this->identicalTo('abc'))
                ->willReturn($validatedMod1);
        $service->expects($this->once())
                ->method('validateMod')
                ->with($this->identicalTo('def'), $this->identicalTo($releases))
                ->willReturn($validatedMod2);

        $result = $service->validate($modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createValidatedBaseMod method.
     * @throws ReflectionException
     * @covers ::createValidatedBaseMod
     */
    public function testCreateValidatedBaseMod(): void
    {
        $baseMod = new Mod();
        $baseMod->setName('abc')
                ->setVersion('1.2.3');

        $expectedResult = new ValidatedMod();
        $expectedResult->setName('abc')
                       ->setVersion('1.2.3');

        $service = new CombinationValidationService($this->combinationService, $this->modPortalService);
        $result = $this->invokeMethod($service, 'createValidatedBaseMod', $baseMod);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createMissingMod method.
     * @throws ReflectionException
     * @covers ::createMissingMod
     */
    public function testCreateMissingMod(): void
    {
        $modName = 'abc';

        $expectedResult = new ValidatedMod();
        $expectedResult->setName('abc')
                       ->setIssueType(ValidatedModIssueType::MISSING_MOD);

        $service = new CombinationValidationService($this->combinationService, $this->modPortalService);
        $result = $this->invokeMethod($service, 'createMissingMod', $modName);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the validateMod method.
     * @throws ReflectionException
     * @covers ::validateMod
     */
    public function testValidateMod(): void
    {
        $modName = 'abc';

        $dependency1 = new Dependency('foo > 1.2.3');
        $dependency2 = new Dependency('bar < 2.3.4');
        $release = new Release();
        $release->setVersion(new Version('3.4.5'));
        $release->getInfoJson()->setDependencies([$dependency1, $dependency2]);

        $releases = [
            'abc' => $release,
            'def' => $this->createMock(Release::class),
        ];

        $expectedResult = new ValidatedMod();
        $expectedResult->setName('abc')
                       ->setVersion('3.4.5')
                       ->setIssueType(ValidatedModIssueType::NONE);

        $service = $this->getMockBuilder(CombinationValidationService::class)
                        ->onlyMethods(['validateDependency'])
                        ->setConstructorArgs([$this->combinationService, $this->modPortalService])
                        ->getMock();
        $service->expects($this->exactly(2))
                ->method('validateDependency')
                ->withConsecutive(
                    [$dependency1, $releases],
                    [$dependency2, $releases],
                )
                ->willReturnOnConsecutiveCalls(
                    ValidatedModIssueType::NONE,
                    ValidatedModIssueType::NONE,
                );

        $result = $this->invokeMethod($service, 'validateMod', $modName, $releases);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the validateMod method.
     * @throws ReflectionException
     * @covers ::validateMod
     */
    public function testValidateModWithMissingRelease(): void
    {
        $modName = 'abc';
        $releases = [
            'def' => $this->createMock(Release::class),
        ];

        $expectedResult = new ValidatedMod();
        $expectedResult->setName('abc')
                       ->setIssueType(ValidatedModIssueType::MISSING_RELEASE);

        $service = $this->getMockBuilder(CombinationValidationService::class)
                        ->onlyMethods(['validateDependency'])
                        ->setConstructorArgs([$this->combinationService, $this->modPortalService])
                        ->getMock();
        $service->expects($this->never())
                ->method('validateDependency');

        $result = $this->invokeMethod($service, 'validateMod', $modName, $releases);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the validateMod method.
     * @throws ReflectionException
     * @covers ::validateMod
     */
    public function testValidateModWithInvalidDependency(): void
    {
        $modName = 'abc';

        $dependency1 = new Dependency('foo > 1.2.3');
        $dependency2 = new Dependency('bar < 2.3.4');
        $release = new Release();
        $release->setVersion(new Version('3.4.5'));
        $release->getInfoJson()->setDependencies([$dependency1, $dependency2]);

        $releases = [
            'abc' => $release,
            'def' => $this->createMock(Release::class),
        ];

        $expectedResult = new ValidatedMod();
        $expectedResult->setName('abc')
                       ->setVersion('3.4.5')
                       ->setIssueType(ValidatedModIssueType::CONFLICT)
                       ->setIssueDependency('bar < 2.3.4');

        $service = $this->getMockBuilder(CombinationValidationService::class)
                        ->onlyMethods(['validateDependency'])
                        ->setConstructorArgs([$this->combinationService, $this->modPortalService])
                        ->getMock();
        $service->expects($this->exactly(2))
                ->method('validateDependency')
                ->withConsecutive(
                    [$dependency1, $releases],
                    [$dependency2, $releases],
                )
                ->willReturnOnConsecutiveCalls(
                    ValidatedModIssueType::NONE,
                    ValidatedModIssueType::CONFLICT,
                );

        $result = $this->invokeMethod($service, 'validateMod', $modName, $releases);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the validateDependency test.
     * @return array<mixed>
     */
    public function provideValidateDependency(): array
    {
        $baseDependency = new Dependency('base > 1.2.3');
        $dependency1 = new Dependency('foo > 1.2.3');
        $dependency2 = new Dependency('!foo');

        $release1 = new Release();
        $release1->setVersion(new Version('0.1.2'));
        $release2 = new Release();
        $release2->setVersion(new Version('2.3.4'));

        return [
            [$baseDependency, [], ValidatedModIssueType::NONE],

            [$dependency1, [], ValidatedModIssueType::MISSING_DEPENDENCY],
            [$dependency1, ['foo' => $release1], ValidatedModIssueType::MISSING_DEPENDENCY],
            [$dependency1, ['foo' => $release2], ValidatedModIssueType::NONE],

            [$dependency2, ['foo' => $release1], ValidatedModIssueType::CONFLICT],
            [$dependency2, [], ValidatedModIssueType::NONE],
        ];
    }

    /**
     * Tests the validateDependency method.
     * @param Dependency $dependency
     * @param array<string,Release> $releases
     * @param string $expectedResult
     * @throws ReflectionException
     * @covers ::validateDependency
     * @dataProvider provideValidateDependency
     */
    public function testValidateDependency(Dependency $dependency, array $releases, string $expectedResult): void
    {
        $service = new CombinationValidationService($this->combinationService, $this->modPortalService);
        $result = $this->invokeMethod($service, 'validateDependency', $dependency, $releases);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the areModsValid test.
     * @return array<mixed>
     */
    public function provideAreModsValid(): array
    {
        $validMod1 = new ValidatedMod();
        $validMod1->setIssueType(ValidatedModIssueType::NONE);
        $validMod2 = new ValidatedMod();
        $validMod2->setIssueType(ValidatedModIssueType::NONE);
        $validMod3 = new ValidatedMod();
        $validMod3->setIssueType(ValidatedModIssueType::NONE);
        $conflict = new ValidatedMod();
        $conflict->setIssueType(ValidatedModIssueType::CONFLICT);
        $missingDependency = new ValidatedMod();
        $missingDependency->setIssueType(ValidatedModIssueType::MISSING_DEPENDENCY);
        $missingMod = new ValidatedMod();
        $missingMod->setIssueType(ValidatedModIssueType::MISSING_MOD);
        $missingRelease = new ValidatedMod();
        $missingRelease->setIssueType(ValidatedModIssueType::MISSING_RELEASE);

        return [
            [[$validMod1, $validMod2, $validMod3], true],
            [[$validMod1, $validMod2, $conflict, $validMod3], false],
            [[$validMod1, $validMod2, $missingDependency, $validMod3], false],
            [[$validMod1, $validMod2, $missingMod, $validMod3], false],
            [[$validMod1, $validMod2, $missingRelease, $validMod3], false],
        ];
    }

    /**
     * Tests the areModsValid method.
     * @param array<ValidatedMod> $validatedMods
     * @param bool $expectedResult
     * @covers ::areModsValid
     * @dataProvider provideAreModsValid
     */
    public function testAreModsValid(array $validatedMods, bool $expectedResult): void
    {
        $service = new CombinationValidationService($this->combinationService, $this->modPortalService);
        $result = $service->areModsValid($validatedMods);

        $this->assertSame($expectedResult, $result);
    }
}
