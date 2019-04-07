<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Collection;

use ArrayIterator;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Data\RecipeData;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RecipeDataCollection class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection
 */
class RecipeDataCollectionTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $collection = new RecipeDataCollection();

        $this->assertSame([], $this->extractProperty($collection, 'values'));
    }

    /**
     * Tests the add method.
     * @throws ReflectionException
     * @covers ::add
     */
    public function testAdd(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData3 */
        $recipeData3 = $this->createMock(RecipeData::class);

        $values = [$recipeData1, $recipeData2];
        $expectedValues = [$recipeData1, $recipeData2, $recipeData3];

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->add($recipeData3);

        $this->assertSame($collection, $result);
        $this->assertSame($expectedValues, $this->extractProperty($collection, 'values'));
    }

    /**
     * Tests the getAllIds method.
     * @throws ReflectionException
     * @covers ::getAllIds
     */
    public function testGetAllIds(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData1->expects($this->once())
                    ->method('getId')
                    ->willReturn(42);

        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        $recipeData2->expects($this->once())
                    ->method('getId')
                    ->willReturn(1337);

        $values = [$recipeData1, $recipeData2];
        $expectedResult = [42, 1337];

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->getAllIds();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the countNames method.
     * @throws ReflectionException
     * @covers ::countNames
     */
    public function testCountNames(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData1->expects($this->once())
                    ->method('getName')
                    ->willReturn('abc');

        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        $recipeData2->expects($this->once())
                    ->method('getName')
                    ->willReturn('def');

        /* @var RecipeData&MockObject $recipeData3 */
        $recipeData3 = $this->createMock(RecipeData::class);
        $recipeData3->expects($this->once())
                    ->method('getName')
                    ->willReturn('abc');

        $values = [$recipeData1, $recipeData2, $recipeData3];
        $expectedResult = 2;

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->countNames();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the filterMode method.
     * @throws ReflectionException
     * @covers ::filterMode
     */
    public function testFilterMode(): void
    {
        $mode = 'abc';

        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData1->expects($this->once())
                    ->method('getMode')
                    ->willReturn('abc');

        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        $recipeData2->expects($this->once())
                    ->method('getMode')
                    ->willReturn('def');

        /* @var RecipeDataCollection&MockObject $newCollection */
        $newCollection = $this->createMock(RecipeDataCollection::class);

        /* @var RecipeDataCollection&MockObject $collection */
        $collection = $this->getMockBuilder(RecipeDataCollection::class)
                           ->setMethods(['filter'])
                           ->getMock();
        $collection->expects($this->once())
                   ->method('filter')
                   ->with($this->callback(function (callable  $callback) use ($recipeData1, $recipeData2): bool {
                       $this->assertTrue($callback($recipeData1));
                       $this->assertFalse($callback($recipeData2));
                       return true;
                   }))
                   ->willReturn($newCollection);

        $result = $collection->filterMode($mode);

        $this->assertSame($newCollection, $result);
    }

    /**
     * Tests the filterItemId method.
     * @throws ReflectionException
     * @covers ::filterItemId
     */
    public function testFilterItemId(): void
    {
        $itemId = 42;

        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData1->expects($this->once())
                    ->method('getItemId')
                    ->willReturn(42);

        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        $recipeData2->expects($this->once())
                    ->method('getItemId')
                    ->willReturn(1337);

        /* @var RecipeDataCollection&MockObject $newCollection */
        $newCollection = $this->createMock(RecipeDataCollection::class);

        /* @var RecipeDataCollection&MockObject $collection */
        $collection = $this->getMockBuilder(RecipeDataCollection::class)
                           ->setMethods(['filter'])
                           ->getMock();
        $collection->expects($this->once())
                   ->method('filter')
                   ->with($this->callback(function (callable  $callback) use ($recipeData1, $recipeData2): bool {
                       $this->assertTrue($callback($recipeData1));
                       $this->assertFalse($callback($recipeData2));
                       return true;
                   }))
                   ->willReturn($newCollection);

        $result = $collection->filterItemId($itemId);

        $this->assertSame($newCollection, $result);
    }

    /**
     * Tests the filter method.
     * @throws ReflectionException
     * @covers ::filter
     */
    public function testFilter(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData3 */
        $recipeData3 = $this->createMock(RecipeData::class);

        $values = [$recipeData1, $recipeData2, $recipeData3];
        $filter = function (RecipeData $recipeData) use ($recipeData2): bool {
            return $recipeData !== $recipeData2;
        };

        $expectedResult = new RecipeDataCollection();
        $expectedResult->add($recipeData1)
                       ->add($recipeData3);

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $this->invokeMethod($collection, 'filter', $filter);

        $this->assertNotSame($collection, $result);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the limitNames method.
     * @throws ReflectionException
     * @covers ::limitNames
     */
    public function testLimitNames(): void
    {
        $numberOfNames = 2;
        $indexOfFirstResult = 1;

        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        $recipeData1->expects($this->once())
                    ->method('getName')
                    ->willReturn('abc');

        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);
        $recipeData2->expects($this->once())
                    ->method('getName')
                    ->willReturn('def');

        /* @var RecipeData&MockObject $recipeData3 */
        $recipeData3 = $this->createMock(RecipeData::class);
        $recipeData3->expects($this->once())
                    ->method('getName')
                    ->willReturn('abc');

        /* @var RecipeData&MockObject $recipeData4 */
        $recipeData4 = $this->createMock(RecipeData::class);
        $recipeData4->expects($this->once())
                    ->method('getName')
                    ->willReturn('ghi');

        /* @var RecipeData&MockObject $recipeData5 */
        $recipeData5 = $this->createMock(RecipeData::class);
        $recipeData5->expects($this->once())
                    ->method('getName')
                    ->willReturn('jkl');

        /* @var RecipeData&MockObject $recipeData6 */
        $recipeData6 = $this->createMock(RecipeData::class);
        $recipeData6->expects($this->once())
                    ->method('getName')
                    ->willReturn('def');

        $values = [$recipeData1, $recipeData2, $recipeData3, $recipeData4, $recipeData5, $recipeData6];

        $expectedResult = new RecipeDataCollection();
        $expectedResult->add($recipeData2)
                       ->add($recipeData6)
                       ->add($recipeData4);

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->limitNames($numberOfNames, $indexOfFirstResult);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getValues method.
     * @throws ReflectionException
     * @covers ::getValues
     */
    public function testGetValues(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);

        $values = [$recipeData1, $recipeData2];

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->getValues();
        $this->assertSame($values, $result);
    }

    /**
     * Tests the getFirstValue method.
     * @throws ReflectionException
     * @covers ::getFirstValue
     */
    public function testGetFirstValue(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);

        $values = [$recipeData1, $recipeData2];

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->getFirstValue();
        $this->assertSame($recipeData1, $result);
    }

    /**
     * Tests the getFirstValue method without any actual values.
     * @throws ReflectionException
     * @covers ::getFirstValue
     */
    public function testGetFirstValueWithoutValues(): void
    {
        $values = [];

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->getFirstValue();
        $this->assertNull($result);
    }

    /**
     * Tests the getIterator method.
     * @throws ReflectionException
     * @covers ::getIterator
     */
    public function testGetIterator(): void
    {
        /* @var RecipeData&MockObject $recipeData1 */
        $recipeData1 = $this->createMock(RecipeData::class);
        /* @var RecipeData&MockObject $recipeData2 */
        $recipeData2 = $this->createMock(RecipeData::class);

        $values = [$recipeData1, $recipeData2];
        $expectedResult = new ArrayIterator($values);

        $collection = new RecipeDataCollection();
        $this->injectProperty($collection, 'values', $values);

        $result = $collection->getIterator();
        $this->assertEquals($expectedResult, $result);
    }
}
