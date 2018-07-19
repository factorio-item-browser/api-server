<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModDependency;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ModDependency class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\ModDependency
 */
class ModDependencyTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $mod = new Mod('abc');
        $requiredMod = new Mod('def');
        $modDependency = new ModDependency($mod, $requiredMod);
        
        $this->assertSame($mod, $modDependency->getMod());
        $this->assertSame($requiredMod, $modDependency->getRequiredMod());
        $this->assertSame('', $modDependency->getRequiredVersion());
        $this->assertSame('', $modDependency->getType());
    }
    
    /**
     * Tests setting and getting the mod.
     * @covers ::getMod
     * @covers ::setMod
     */
    public function testSetAndGetMod()
    {
        $modDependency = new ModDependency(new Mod('foo'), new Mod('bar'));
    
        $mod = new Mod('abc');
        $this->assertSame($modDependency, $modDependency->setMod($mod));
        $this->assertSame($mod, $modDependency->getMod());
    }
    
    /**
     * Tests setting and getting the requiredMod.
     * @covers ::getRequiredMod
     * @covers ::setRequiredMod
     */
    public function testSetAndGetRequiredMod()
    {
        $modDependency = new ModDependency(new Mod('foo'), new Mod('bar'));
    
        $requiredMod = new Mod('abc');
        $this->assertSame($modDependency, $modDependency->setRequiredMod($requiredMod));
        $this->assertSame($requiredMod, $modDependency->getRequiredMod());
    }
    
    /**
     * Tests setting and getting the requiredVersion.
     * @covers ::getRequiredVersion
     * @covers ::setRequiredVersion
     */
    public function testSetAndGetRequiredVersion()
    {
        $modDependency = new ModDependency(new Mod('foo'), new Mod('bar'));
    
        $requiredVersion = '1.2.3';
        $this->assertSame($modDependency, $modDependency->setRequiredVersion($requiredVersion));
        $this->assertSame($requiredVersion, $modDependency->getRequiredVersion());
    }
    
    /**
     * Tests setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType()
    {
        $modDependency = new ModDependency(new Mod('foo'), new Mod('bar'));
    
        $type = 'abc';
        $this->assertSame($modDependency, $modDependency->setType($type));
        $this->assertSame($type, $modDependency->getType());
    }
}
