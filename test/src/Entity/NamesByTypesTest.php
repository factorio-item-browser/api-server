<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Entity;

use ArrayIterator;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Entity\NamesByTypes;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the NamesByTypes class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Entity\NamesByTypes
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
        $entity = new NamesByTypes();

        $this->assertSame([], $this->extractProperty($entity, 'values'));
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

        $entity = new NamesByTypes();
        $this->injectProperty($entity, 'values', $values);

        $this->assertSame($entity, $entity->addName('abc', 'ghi'));
        $this->assertSame($entity, $entity->addName('jkl', 'mno'));

        $this->assertEquals($expectedValues, $this->extractProperty($entity, 'values'));
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

        $entity = new NamesByTypes();
        $this->injectProperty($entity, 'values', $values);

        $this->assertSame($entity, $entity->setNames($type, $names));

        $this->assertEquals($expectedValues, $this->extractProperty($entity, 'values'));
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

        $entity = new NamesByTypes();
        $this->injectProperty($entity, 'values', $values);

        $this->assertEquals(['def', 'ghi'], $entity->getNames('abc'));
        $this->assertEquals([], $entity->getNames('foo'));
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

        $entity = new NamesByTypes();
        $this->injectProperty($entity, 'values', $values);

        $this->assertTrue($entity->hasName('abc', 'def'));
        $this->assertFalse($entity->hasName('jkl', 'foo'));
        $this->assertFalse($entity->hasName('foo', 'bar'));
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

        $entity = new NamesByTypes();
        $this->injectProperty($entity, 'values', $values);

        $this->assertSame($values, $entity->toArray());
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

        $entity = new NamesByTypes();
        $this->injectProperty($entity, 'values', $values);

        $result = $entity->getIterator();
        $this->assertEquals($expectedResult, $result);
    }
}
