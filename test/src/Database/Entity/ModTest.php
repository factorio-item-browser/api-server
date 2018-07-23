<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Mod class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\Mod
 */
class ModTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getDependencies
     * @covers ::getCombinations
     */
    public function testConstruct()
    {
        $name = 'abc';
        $mod = new Mod($name);

        $this->assertNull($mod->getId());
        $this->assertSame($name, $mod->getName());
        $this->assertSame('', $mod->getAuthor());
        $this->assertSame('', $mod->getCurrentVersion());
        $this->assertSame(0, $mod->getOrder());
        $this->assertInstanceOf(ArrayCollection::class, $mod->getDependencies());
        $this->assertInstanceOf(ArrayCollection::class, $mod->getCombinations());
    }

    /**
     * Tests setting and getting the id.
     * @covers ::getId
     * @covers ::setId
     */
    public function testSetAndGetId()
    {
        $mod = new Mod('foo');

        $id = 42;
        $this->assertSame($mod, $mod->setId($id));
        $this->assertSame($id, $mod->getId());
    }

    /**
     * Tests setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName()
    {
        $mod = new Mod('foo');

        $name = 'abc';
        $this->assertSame($mod, $mod->setName($name));
        $this->assertSame($name, $mod->getName());
    }

    /**
     * Tests setting and getting the author.
     * @covers ::getAuthor
     * @covers ::setAuthor
     */
    public function testSetAndGetAuthor()
    {
        $mod = new Mod('foo');

        $author = 'abc';
        $this->assertSame($mod, $mod->setAuthor($author));
        $this->assertSame($author, $mod->getAuthor());
    }

    /**
     * Tests setting and getting the currentVersion.
     * @covers ::getCurrentVersion
     * @covers ::setCurrentVersion
     */
    public function testSetAndGetCurrentVersion()
    {
        $mod = new Mod('foo');

        $currentVersion = '1.2.3';
        $this->assertSame($mod, $mod->setCurrentVersion($currentVersion));
        $this->assertSame($currentVersion, $mod->getCurrentVersion());
    }

    /**
     * Tests setting and getting the order.
     * @covers ::getOrder
     * @covers ::setOrder
     */
    public function testSetAndGetOrder()
    {
        $mod = new Mod('foo');

        $order = 42;
        $this->assertSame($mod, $mod->setOrder($order));
        $this->assertSame($order, $mod->getOrder());
    }
}
