<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Helper;

use FactorioItemBrowser\Api\Database\Constant\ModDependencyType;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Entity\ModDependency;
use FactorioItemBrowser\Api\Server\Database\Helper\ModDependencyResolver;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModDependencyResolver class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Helper\ModDependencyResolver
 */
class ModDependencyResolverTest extends TestCase
{
    /**
     * Tests the resolve method.
     * @covers ::__construct
     * @covers ::resolve
     * @covers ::<protected>
     */
    public function testResolve()
    {
        $modNames = ['abc', 'def'];

        $mod1 = new Mod('abc');
        $mod2 = new Mod('def');
        $mod3 = new Mod('ghi');
        $mod4 = new Mod('jkl');

        $modDependency2a = new ModDependency($mod2, $mod1);
        $modDependency2a->setType(ModDependencyType::MANDATORY);
        $modDependency2b = new ModDependency($mod2, $mod3);
        $modDependency2b->setType(ModDependencyType::MANDATORY);
        $modDependency2c = new ModDependency($mod2, $mod4);
        $modDependency2c->setType(ModDependencyType::OPTIONAL);
        $modDependency3a = new ModDependency($mod3, $mod1);
        $modDependency3a->setType(ModDependencyType::MANDATORY);

        $mod2->getDependencies()->add($modDependency2a);
        $mod2->getDependencies()->add($modDependency2b);
        $mod2->getDependencies()->add($modDependency2c);
        $mod3->getDependencies()->add($modDependency3a);

        /* @var ModService|MockObject $modService */
        $modService = $this->getMockBuilder(ModService::class)
                           ->setMethods(['getModsWithDependencies'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $modService->expects($this->exactly(2))
                   ->method('getModsWithDependencies')
                   ->withConsecutive(
                       [['abc', 'def']],
                       [['ghi']]
                   )
                   ->willReturnOnConsecutiveCalls(
                       ['abc' => $mod1, 'def' => $mod2],
                       ['ghi' => $mod3]
                   );

        $expectedResult = ['abc', 'ghi', 'def'];

        $resolver = new ModDependencyResolver($modService);
        $result = $resolver->resolve($modNames);
        $this->assertSame($expectedResult, $result);
    }
}
