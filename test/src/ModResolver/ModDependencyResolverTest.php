<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\ModResolver;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Database\Constant\ModDependencyType;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Entity\ModDependency;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
use FactorioItemBrowser\Api\Server\ModResolver\ModDependencyResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ModDependencyResolver class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\ModResolver\ModDependencyResolver
 */
class ModDependencyResolverTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mod repository.
     * @var ModRepository&MockObject
     */
    protected $modRepository;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modRepository = $this->createMock(ModRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $resolver = new ModDependencyResolver($this->modRepository);

        $this->assertSame($this->modRepository, $this->extractProperty($resolver, 'modRepository'));
    }

    /**
     * Tests the resolve method.
     * @throws ReflectionException
     * @covers ::resolve
     */
    public function testResolve(): void
    {
        $modNames = ['abc', 'def'];
        $resolvedMods = [
            'ghi' => $this->createMock(Mod::class),
            'jkl' => $this->createMock(Mod::class),
        ];
        $expectedResult = ['ghi', 'jkl'];

        /* @var ModDependencyResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModDependencyResolver::class)
                         ->setMethods(['reset', 'fetchMods', 'processModWithName'])
                         ->setConstructorArgs([$this->modRepository])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('reset');
        $resolver->expects($this->once())
                 ->method('fetchMods')
                 ->with($this->identicalTo($modNames));
        $resolver->expects($this->exactly(2))
                 ->method('processModWithName')
                 ->withConsecutive(
                     [$this->identicalTo('abc')],
                     [$this->identicalTo('def')]
                 );
        $this->injectProperty($resolver, 'resolvedMods', $resolvedMods);

        $result = $resolver->resolve($modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the reset method.
     * @throws ReflectionException
     * @covers ::reset
     */
    public function testReset(): void
    {
        $resolver = new ModDependencyResolver($this->modRepository);
        $this->injectProperty($resolver, 'resolvedMods', ['fail']);

        $this->invokeMethod($resolver, 'reset');
        $this->assertEquals([], $this->extractProperty($resolver, 'resolvedMods'));
    }

    /**
     * Tests the fetchMods method.
     * @throws ReflectionException
     * @covers ::fetchMods
     */
    public function testFetchMods(): void
    {
        /* @var Mod&MockObject $mod1 */
        $mod1 = $this->createMock(Mod::class);
        /* @var Mod&MockObject $mod2 */
        $mod2 = $this->createMock(Mod::class);
        /* @var Mod&MockObject $mod3 */
        $mod3 = $this->createMock(Mod::class);
        /* @var Mod&MockObject $mod4 */
        $mod4 = $this->createMock(Mod::class);

        $fetchedMods = [
            'abc' => $mod1,
            'def' => $mod2,
        ];
        $newFetchedMods = [
            'ghi' => $mod3,
            'jkl' => $mod4,
        ];
        $expectedFetchedMods = [
            'abc' => $mod1,
            'def' => $mod2,
            'ghi' => $mod3,
            'jkl' => $mod4,
        ];
        $modNames = ['abc', 'def', 'ghi', 'jkl'];
        $expectedModNames = ['ghi', 'jkl'];

        /* @var ModDependencyResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModDependencyResolver::class)
                         ->setMethods(['fetchModsWithDependencies'])
                         ->setConstructorArgs([$this->modRepository])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('fetchModsWithDependencies')
                 ->with($this->identicalTo($expectedModNames))
                 ->willReturn($newFetchedMods);
        $this->injectProperty($resolver, 'fetchedMods', $fetchedMods);

        $this->invokeMethod($resolver, 'fetchMods', $modNames);

        $this->assertEquals($expectedFetchedMods, $this->extractProperty($resolver, 'fetchedMods'));
    }

    /**
     * Tests the fetchModsWithDependencies method.
     * @throws ReflectionException
     * @covers ::fetchModsWithDependencies
     */
    public function testFetchModsWithDependencies(): void
    {
        $modNames = ['abc', 'def'];

        /* @var Mod&MockObject $mod1 */
        $mod1 = $this->createMock(Mod::class);
        $mod1->expects($this->once())
             ->method('getName')
             ->willReturn('ghi');
        
        /* @var Mod&MockObject $mod2 */
        $mod2 = $this->createMock(Mod::class);
        $mod2->expects($this->once())
             ->method('getName')
             ->willReturn('jkl');

        $mods = [$mod1, $mod2];
        $expectedResult = [
            'ghi' => $mod1,
            'jkl' => $mod2,
        ];
        
        $this->modRepository->expects($this->once())
                            ->method('findByNamesWithDependencies')
                            ->with($this->identicalTo($modNames))
                            ->willReturn($mods);

        $resolver = new ModDependencyResolver($this->modRepository);
        $result = $this->invokeMethod($resolver, 'fetchModsWithDependencies', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the processModWithName method.
     * @throws ReflectionException
     * @covers ::processModWithName
     */
    public function testProcessModWithName(): void
    {
        $modName = 'abc';
        $resolvedMods = [
            'def' => true,
            'ghi' => true,
        ];
        $expectedResolvedMods = [
            'def' => true,
            'ghi' => true,
            'abc' => true,
        ];

        /* @var ModDependency&MockObject $dependency1 */
        $dependency1 = $this->createMock(ModDependency::class);
        /* @var ModDependency&MockObject $dependency2 */
        $dependency2 = $this->createMock(ModDependency::class);

        $dependencies = new ArrayCollection([$dependency1, $dependency2]);

        /* @var Mod&MockObject $mod */
        $mod = $this->createMock(Mod::class);
        $mod->expects($this->once())
            ->method('getDependencies')
            ->willReturn($dependencies);

        $fetchedMods = ['abc' => $mod];

        /* @var ModDependencyResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModDependencyResolver::class)
                         ->setMethods(['fetchMods', 'processDependency'])
                         ->setConstructorArgs([$this->modRepository])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('fetchMods')
                 ->with($this->identicalTo([$modName]));
        $resolver->expects($this->exactly(2))
                 ->method('processDependency')
                 ->withConsecutive(
                     [$this->identicalTo($dependency1)],
                     [$this->identicalTo($dependency2)]
                 );
        $this->injectProperty($resolver, 'fetchedMods', $fetchedMods);
        $this->injectProperty($resolver, 'resolvedMods', $resolvedMods);

        $this->invokeMethod($resolver, 'processModWithName', $modName);

        $this->assertEquals($expectedResolvedMods, $this->extractProperty($resolver, 'resolvedMods'));
    }

    /**
     * Tests the processModWithName method.
     * @throws ReflectionException
     * @covers ::processModWithName
     */
    public function testProcessModWithNameWithoutFetchedMod(): void
    {
        $modName = 'abc';
        $resolvedMods = [
            'def' => true,
            'ghi' => true,
        ];

        $fetchedMods = [
            'def' => $this->createMock(Mod::class),
        ];

        /* @var ModDependencyResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModDependencyResolver::class)
                         ->setMethods(['fetchMods', 'processDependency'])
                         ->setConstructorArgs([$this->modRepository])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('fetchMods')
                 ->with($this->identicalTo([$modName]));
        $resolver->expects($this->never())
                 ->method('processDependency');

        $this->injectProperty($resolver, 'fetchedMods', $fetchedMods);
        $this->injectProperty($resolver, 'resolvedMods', $resolvedMods);

        $this->invokeMethod($resolver, 'processModWithName', $modName);

        $this->assertEquals($resolvedMods, $this->extractProperty($resolver, 'resolvedMods'));
    }

    /**
     * Tests the processDependency method.
     * @throws ReflectionException
     * @covers ::processDependency
     */
    public function testProcessDependency(): void
    {
        $type = ModDependencyType::MANDATORY;
        $requiredModName = 'abc';
        $resolvedMods = [
            'def' => true,
        ];

        /* @var Mod&MockObject $mod */
        $requiredMod = $this->createMock(Mod::class);
        $requiredMod->expects($this->once())
                    ->method('getName')
                    ->willReturn($requiredModName);

        /* @var ModDependency&MockObject $dependency */
        $dependency = $this->createMock(ModDependency::class);
        $dependency->expects($this->once())
                   ->method('getType')
                   ->willReturn($type);
        $dependency->expects($this->once())
                   ->method('getRequiredMod')
                   ->willReturn($requiredMod);

        /* @var ModDependencyResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModDependencyResolver::class)
                         ->setMethods(['processModWithName'])
                         ->setConstructorArgs([$this->modRepository])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('processModWithName')
                 ->with($this->identicalTo($requiredModName));
        $this->injectProperty($resolver, 'resolvedMods', $resolvedMods);

        $this->invokeMethod($resolver, 'processDependency', $dependency);
    }

    /**
     * Tests the processDependency method with an optional dependency type.
     * @throws ReflectionException
     * @covers ::processDependency
     */
    public function testProcessDependencyWithOptionalType(): void
    {
        $type = ModDependencyType::OPTIONAL;
        $requiredModName = 'abc';
        $resolvedMods = [
            'def' => true,
        ];

        /* @var Mod&MockObject $mod */
        $requiredMod = $this->createMock(Mod::class);
        $requiredMod->expects($this->once())
                    ->method('getName')
                    ->willReturn($requiredModName);

        /* @var ModDependency&MockObject $dependency */
        $dependency = $this->createMock(ModDependency::class);
        $dependency->expects($this->once())
                   ->method('getType')
                   ->willReturn($type);
        $dependency->expects($this->once())
                   ->method('getRequiredMod')
                   ->willReturn($requiredMod);

        /* @var ModDependencyResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModDependencyResolver::class)
                         ->setMethods(['processModWithName'])
                         ->setConstructorArgs([$this->modRepository])
                         ->getMock();
        $resolver->expects($this->never())
                 ->method('processModWithName');
        $this->injectProperty($resolver, 'resolvedMods', $resolvedMods);

        $this->invokeMethod($resolver, 'processDependency', $dependency);
    }


    /**
     * Tests the processDependency method with its mod already being resolved.
     * @throws ReflectionException
     * @covers ::processDependency
     */
    public function testProcessDependencyWithResolvedMod(): void
    {
        $type = ModDependencyType::MANDATORY;
        $requiredModName = 'abc';
        $resolvedMods = [
            'abc' => true,
            'def' => true,
        ];

        /* @var Mod&MockObject $mod */
        $requiredMod = $this->createMock(Mod::class);
        $requiredMod->expects($this->once())
                    ->method('getName')
                    ->willReturn($requiredModName);

        /* @var ModDependency&MockObject $dependency */
        $dependency = $this->createMock(ModDependency::class);
        $dependency->expects($this->once())
                   ->method('getType')
                   ->willReturn($type);
        $dependency->expects($this->once())
                   ->method('getRequiredMod')
                   ->willReturn($requiredMod);

        /* @var ModDependencyResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ModDependencyResolver::class)
                         ->setMethods(['processModWithName'])
                         ->setConstructorArgs([$this->modRepository])
                         ->getMock();
        $resolver->expects($this->never())
                 ->method('processModWithName');
        $this->injectProperty($resolver, 'resolvedMods', $resolvedMods);

        $this->invokeMethod($resolver, 'processDependency', $dependency);
    }
}
