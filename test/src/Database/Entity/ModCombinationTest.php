<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination;
use PHPUnit\Framework\TestCase;

/**
 * The PHUnit test of the ModCombination class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\ModCombination
 */
class ModCombinationTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getIcons
     * @covers ::getItems
     * @covers ::getMachines
     * @covers ::getRecipes
     * @covers ::getTranslations
     */
    public function testConstruct()
    {
        $mod = new Mod('abc');
        $modCombination = new ModCombination($mod);

        $this->assertSame(null, $modCombination->getId());
        $this->assertSame($mod, $modCombination->getMod());
        $this->assertSame([], $modCombination->getOptionalModIds());
        $this->assertSame('', $modCombination->getName());
        $this->assertSame(0, $modCombination->getOrder());
        $this->assertInstanceOf(ArrayCollection::class, $modCombination->getItems());
        $this->assertInstanceOf(ArrayCollection::class, $modCombination->getRecipes());
        $this->assertInstanceOf(ArrayCollection::class, $modCombination->getMachines());
        $this->assertInstanceOf(ArrayCollection::class, $modCombination->getTranslations());
        $this->assertInstanceOf(ArrayCollection::class, $modCombination->getIcons());
    }

    /**
     * Tests setting and getting the id.
     * @covers ::getId
     * @covers ::setId
     */
    public function testSetAndGetId()
    {
        $modCombination = new ModCombination(new Mod('foo'));

        $id = 42;
        $this->assertSame($modCombination, $modCombination->setId($id));
        $this->assertSame($id, $modCombination->getId());
    }

    /**
     * Tests setting and getting the mod.
     * @covers ::getMod
     * @covers ::setMod
     */
    public function testSetAndGetMod()
    {
        $modCombination = new ModCombination(new Mod('foo'));

        $mod = new Mod('abc');
        $this->assertSame($modCombination, $modCombination->setMod($mod));
        $this->assertSame($mod, $modCombination->getMod());
    }

    /**
     * Tests setting and getting the optionalModIds.
     * @covers ::getOptionalModIds
     * @covers ::setOptionalModIds
     */
    public function testSetAndGetOptionalModIds()
    {
        $modCombination = new ModCombination(new Mod('foo'));

        $optionalModIds = [42, 1337];
        $this->assertSame($modCombination, $modCombination->setOptionalModIds($optionalModIds));
        $this->assertSame($optionalModIds, $modCombination->getOptionalModIds());
    }

    /**
     * Tests setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName()
    {
        $modCombination = new ModCombination(new Mod('foo'));

        $name = 'abc';
        $this->assertSame($modCombination, $modCombination->setName($name));
        $this->assertSame($name, $modCombination->getName());
    }

    /**
     * Tests setting and getting the order.
     * @covers ::getOrder
     * @covers ::setOrder
     */
    public function testSetAndGetOrder()
    {
        $modCombination = new ModCombination(new Mod('foo'));

        $order = 42;
        $this->assertSame($modCombination, $modCombination->setOrder($order));
        $this->assertSame($order, $modCombination->getOrder());
    }
}
