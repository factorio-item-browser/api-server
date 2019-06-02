<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\ModResolver;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Database\Repository\ModCombinationRepository;
use FactorioItemBrowser\Api\Server\ModResolver\ModCombinationResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModCombinationResolver class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\ModResolver\ModCombinationResolver
 */
class ModCombinationResolverTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mod combination repository.
     * @var ModCombinationRepository&MockObject
     */
    protected $modCombinationRepository;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modCombinationRepository = $this->createMock(ModCombinationRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $resolver = new ModCombinationResolver($this->modCombinationRepository);

        $this->assertSame(
            $this->modCombinationRepository,
            $this->extractProperty($resolver, 'modCombinationRepository')
        );
    }

    /**
     * Tests the resolve method.
     * @throws ReflectionException
     * @covers ::resolve
     */
    public function testResolve(): void
    {
        $modNames = ['abc', 'def'];
        $combinations = [
            42 => $this->createMock(ModCombination::class),
            1337 => $this->createMock(ModCombination::class),
        ];
        $mods = [
            21 => $this->createMock(Mod::class),
            27 => $this->createMock(Mod::class),
        ];
        $cleanedCombinations = [
            24 => $this->createMock(ModCombination::class),
            7331 => $this->createMock(ModCombination::class),
        ];
        $expectedResult = [24, 7331];

        /* @var ModCombinationResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModCombinationResolver::class)
                         ->setMethods(['fetchCombinations', 'extractMods', 'removeInvalidCombinations'])
                         ->setConstructorArgs([$this->modCombinationRepository])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('fetchCombinations')
                 ->with($this->identicalTo($modNames))
                 ->willReturn($combinations);
        $resolver->expects($this->once())
                 ->method('extractMods')
                 ->with($this->identicalTo($combinations))
                 ->willReturn($mods);
        $resolver->expects($this->once())
                 ->method('removeInvalidCombinations')
                 ->with($this->identicalTo($combinations), $this->identicalTo($mods))
                 ->willReturn($cleanedCombinations);

        $result = $resolver->resolve($modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the fetchCombinations method.
     * @throws ReflectionException
     * @covers ::fetchCombinations
     */
    public function testFetchCombinations(): void
    {
        $modNames = ['abc', 'def'];

        /* @var ModCombination&MockObject $combination1 */
        $combination1 = $this->createMock(ModCombination::class);
        $combination1->expects($this->once())
                     ->method('getId')
                     ->willReturn(42);

        /* @var ModCombination&MockObject $combination2 */
        $combination2 = $this->createMock(ModCombination::class);
        $combination2->expects($this->once())
                     ->method('getId')
                     ->willReturn(1337);

        $combinations = [$combination1, $combination2];
        $expectedResult = [
            42 => $combination1,
            1337 => $combination2,
        ];

        $this->modCombinationRepository->expects($this->once())
                                       ->method('findByModNames')
                                       ->with($this->identicalTo($modNames))
                                       ->willReturn($combinations);

        $resolver = new ModCombinationResolver($this->modCombinationRepository);
        $result = $this->invokeMethod($resolver, 'fetchCombinations', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the extractMods method.
     * @throws ReflectionException
     * @covers ::extractMods
     */
    public function testExtractMods(): void
    {
        /* @var Mod&MockObject $mod1 */
        $mod1 = $this->createMock(Mod::class);
        $mod1->expects($this->once())
             ->method('getId')
             ->willReturn(42);

        /* @var Mod&MockObject $mod2 */
        $mod2 = $this->createMock(Mod::class);
        $mod2->expects($this->once())
             ->method('getId')
             ->willReturn(1337);

        $expectedResult = [
            42 => $mod1,
            1337 => $mod2,
        ];

        /* @var ModCombination&MockObject $combination1 */
        $combination1 = $this->createMock(ModCombination::class);
        $combination1->expects($this->atLeastOnce())
                     ->method('getMod')
                     ->willReturn($mod1);

        /* @var ModCombination&MockObject $combination2 */
        $combination2 = $this->createMock(ModCombination::class);
        $combination2->expects($this->atLeastOnce())
                     ->method('getMod')
                     ->willReturn($mod2);

        $combinations = [$combination1, $combination2];

        $resolver = new ModCombinationResolver($this->modCombinationRepository);
        $result = $this->invokeMethod($resolver, 'extractMods', $combinations);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the removeInvalidCombinations method.
     * @throws ReflectionException
     * @covers ::removeInvalidCombinations
     */
    public function testRemoveInvalidCombinations(): void
    {
        /* @var ModCombination&MockObject $combination1 */
        $combination1 = $this->createMock(ModCombination::class);
        /* @var ModCombination&MockObject $combination2 */
        $combination2 = $this->createMock(ModCombination::class);
        /* @var ModCombination&MockObject $combination3 */
        $combination3 = $this->createMock(ModCombination::class);

        $combinations = [
            42 => $combination1,
            21 => $combination2,
            1337 => $combination3,
        ];
        $mods = [
            $this->createMock(Mod::class),
            $this->createMock(Mod::class),
        ];
        $expectedResult = [
            42 => $combination1,
            1337 => $combination3,
        ];

        /* @var ModCombinationResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModCombinationResolver::class)
                         ->setMethods(['isCombinationValid'])
                         ->setConstructorArgs([$this->modCombinationRepository])
                         ->getMock();
        $resolver->expects($this->exactly(3))
                 ->method('isCombinationValid')
                 ->withConsecutive(
                     [$this->identicalTo($combination1), $this->identicalTo($mods)],
                     [$this->identicalTo($combination2), $this->identicalTo($mods)],
                     [$this->identicalTo($combination3), $this->identicalTo($mods)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     true,
                     false,
                     true
                 );

        $result = $this->invokeMethod($resolver, 'removeInvalidCombinations', $combinations, $mods);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the isCombinationValid method.
     * @throws ReflectionException
     * @covers ::isCombinationValid
     */
    public function testIsCombinationValid(): void
    {
        $optionalModIds = [42, 1337];
        $mods = [
            42 => $this->createMock(Mod::class),
            21 => $this->createMock(Mod::class),
            1337 => $this->createMock(Mod::class),
        ];

        /* @var ModCombination&MockObject $combination */
        $combination = $this->createMock(ModCombination::class);
        $combination->expects($this->once())
                    ->method('getOptionalModIds')
                    ->willReturn($optionalModIds);

        $resolver = new ModCombinationResolver($this->modCombinationRepository);
        $result = $this->invokeMethod($resolver, 'isCombinationValid', $combination, $mods);

        $this->assertTrue($result);
    }

    /**
     * Tests the isCombinationValid method with an invalid combination.
     * @throws ReflectionException
     * @covers ::isCombinationValid
     */
    public function testIsCombinationValidWithInvalidCombination(): void
    {
        $optionalModIds = [42, 1337];
        $mods = [
            42 => $this->createMock(Mod::class),
            21 => $this->createMock(Mod::class),
        ];

        /* @var ModCombination&MockObject $combination */
        $combination = $this->createMock(ModCombination::class);
        $combination->expects($this->once())
                    ->method('getOptionalModIds')
                    ->willReturn($optionalModIds);

        $resolver = new ModCombinationResolver($this->modCombinationRepository);
        $result = $this->invokeMethod($resolver, 'isCombinationValid', $combination, $mods);

        $this->assertFalse($result);
    }
}
