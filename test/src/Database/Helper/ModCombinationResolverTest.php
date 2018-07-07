<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Helper;

use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Server\Database\Helper\ModCombinationResolver;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModCombinationResolver class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Helper\ModCombinationResolver
 */
class ModCombinationResolverTest extends TestCase
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
        $mod1->setId(42);
        $mod2 = new Mod('def');
        $mod2->setId(1337);

        $modCombination1 = new ModCombination($mod1);
        $modCombination1->setId(27);
        $modCombination2 = new ModCombination($mod2);
        $modCombination2->setId(35)
                        ->setOptionalModIds([42]);
        $modCombination3 = new ModCombination($mod2);
        $modCombination3->setId(42)
                        ->setOptionalModIds([21]);

        $modCombinations = [
            27 => $modCombination1,
            35 => $modCombination2,
            42 => $modCombination3
        ];

        $expectedResult = [27, 35];

        /* @var ModService|MockObject $modService */
        $modService = $this->getMockBuilder(ModService::class)
                           ->setMethods(['getModCombinationsByModNames'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $modService->expects($this->once())
                   ->method('getModCombinationsByModNames')
                   ->with($modNames)
                   ->willReturn($modCombinations);

        $resolver = new ModCombinationResolver($modService);
        $result = $resolver->resolve($modNames);
        $this->assertEquals($expectedResult, $result);
    }
}
