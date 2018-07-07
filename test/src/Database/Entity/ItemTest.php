<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FactorioItemBrowser\Api\Server\Database\Entity\Item;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Item class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Entity\Item
 */
class ItemTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getModCombinations
     */
    public function testConstruct()
    {
        $item = new Item('abc', 'def');

        $this->assertSame(null, $item->getId());
        $this->assertSame('abc', $item->getType());
        $this->assertSame('def', $item->getName());
        $this->assertInstanceOf(ArrayCollection::class, $item->getModCombinations());
    }

    /**
     * Tests setting and getting the id.
     * @covers ::getId
     * @covers ::setId
     */
    public function testSetAndGetId()
    {
        $item = new Item('foo', 'bar');

        $id = 42;
        $this->assertSame($item, $item->setId($id));
        $this->assertSame($id, $item->getId());
    }

    /**
     * Tests setting and getting the type.
     * @covers ::getType
     * @covers ::setType
     */
    public function testSetAndGetType()
    {
        $item = new Item('foo', 'bar');

        $type = 'abc';
        $this->assertSame($item, $item->setType($type));
        $this->assertSame($type, $item->getType());
    }

    /**
     * Tests setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName()
    {
        $item = new Item('foo', 'bar');

        $name = 'abc';
        $this->assertSame($item, $item->setName($name));
        $this->assertSame($name, $item->getName());
    }
}
