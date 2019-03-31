<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Collection;

use ArrayIterator;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Collection\NamesByTypes;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the NamesByTypes class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Collection\NamesByTypes
 */
class NamesByTypesTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $collection = new NamesByTypes();

        $this->assertSame([], $this->extractProperty($collection, 'values'));
    }

    /**
     * Tests the addName method.
     * @throws ReflectionException
     * @covers ::addName
     */
    public function testAddName(): void
    {
        $values = [
            'abc' => ['def']
        ];
        $expectedValues = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];

        $collection = new NamesByTypes();
        $this->injectProperty($collection, 'values', $values);

        $this->assertSame($collection, $collection->addName('abc', 'ghi'));
        $this->assertSame($collection, $collection->addName('jkl', 'mno'));

        $this->assertEquals($expectedValues, $this->extractProperty($collection, 'values'));
    }

    /**
     * Tests the setNames method.
     * @throws ReflectionException
     * @covers ::setNames
     */
    public function testSetNames(): void
    {
        $values = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];
        $expectedValues = [
            'abc' => ['pqr', 'stu'],
            'jkl' => ['mno'],
        ];

        $type = 'abc';
        $names = ['pqr', 'stu'];

        $collection = new NamesByTypes();
        $this->injectProperty($collection, 'values', $values);

        $this->assertSame($collection, $collection->setNames($type, $names));

        $this->assertEquals($expectedValues, $this->extractProperty($collection, 'values'));
    }

    /**
     * Tests the getNames method.
     * @throws ReflectionException
     * @covers ::getNames
     */
    public function testGetNames(): void
    {
        $values = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];

        $collection = new NamesByTypes();
        $this->injectProperty($collection, 'values', $values);

        $this->assertEquals(['def', 'ghi'], $collection->getNames('abc'));
        $this->assertEquals([], $collection->getNames('foo'));
    }

    /**
     * Tests the hasName method.
     * @throws ReflectionException
     * @covers ::hasName
     */
    public function testHasName(): void
    {
        $values = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];

        $collection = new NamesByTypes();
        $this->injectProperty($collection, 'values', $values);

        $this->assertTrue($collection->hasName('abc', 'def'));
        $this->assertFalse($collection->hasName('jkl', 'foo'));
        $this->assertFalse($collection->hasName('foo', 'bar'));
    }

    /**
     * Tests the toArray method.
     * @throws ReflectionException
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        $values = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];

        $collection = new NamesByTypes();
        $this->injectProperty($collection, 'values', $values);

        $this->assertSame($values, $collection->toArray());
    }

    /**
     * Tests the getIterator method.
     * @throws ReflectionException
     * @covers ::getIterator
     */
    public function testGetIterator(): void
    {
        $values = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];
        $expectedResult = new ArrayIterator($values);

        $collection = new NamesByTypes();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->getIterator();
        $this->assertEquals($expectedResult, $result);
    }
}
