<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Traits;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Transfer\Entity;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the TypeAndNameFromEntityExtractorTrait class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait
 */
class TypeAndNameFromEntityExtractorTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @param array<string> $mockedMethods
     * @return MockObject
     */
    private function createInstance(array $mockedMethods = []): MockObject
    {
        return $this->getMockBuilder(TypeAndNameFromEntityExtractorTrait::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMockForTrait();
    }

    /**
     * @throws ReflectionException
     */
    public function testExtractTypesAndNames(): void
    {
        $entity1 = new Entity();
        $entity1->type = 'abc';
        $entity1->name = 'def';

        $entity2 = new Entity();
        $entity2->type = 'abc';
        $entity2->name = 'ghi';

        $entity3 = new Entity();
        $entity3->type = 'jkl';
        $entity3->name = 'mno';

        $entities = [$entity1, $entity2, $entity3];
        $expectedResult = new NamesByTypes();
        $expectedResult->addName('abc', 'def')
                       ->addName('abc', 'ghi')
                       ->addName('jkl', 'mno');

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'extractTypesAndNames', $entities);

        $this->assertEquals($expectedResult, $result);
    }
}
