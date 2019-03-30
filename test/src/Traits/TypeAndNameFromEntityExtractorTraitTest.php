<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Traits;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Server\Entity\NamesByTypes;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the TypeAndNameFromEntityExtractorTrait class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait
 */
class TypeAndNameFromEntityExtractorTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the extractTypesAndNames method.
     * @throws ReflectionException
     * @covers ::extractTypesAndNames
     */
    public function testExtractTypesAndNames(): void
    {
        /* @var Entity&MockObject $entity1 */
        $entity1 = $this->createMock(Entity::class);
        $entity1->expects($this->once())
                ->method('getType')
                ->willReturn('abc');
        $entity1->expects($this->once())
                ->method('getName')
                ->willReturn('def');

        /* @var Entity&MockObject $entity2 */
        $entity2 = $this->createMock(Entity::class);
        $entity2->expects($this->once())
                ->method('getType')
                ->willReturn('abc');
        $entity2->expects($this->once())
                ->method('getName')
                ->willReturn('ghi');

        /* @var Entity&MockObject $entity3 */
        $entity3 = $this->createMock(Entity::class);
        $entity3->expects($this->once())
                ->method('getType')
                ->willReturn('jkl');
        $entity3->expects($this->once())
                ->method('getName')
                ->willReturn('mno');

        $entities = [$entity1, $entity2, $entity3];
        $expectedResult = new NamesByTypes();
        $expectedResult->addName('abc', 'def')
                       ->addName('abc', 'ghi')
                       ->addName('jkl', 'mno');

        /* @var TypeAndNameFromEntityExtractorTrait&MockObject $trait */
        $trait = $this->getMockBuilder(TypeAndNameFromEntityExtractorTrait::class)
                      ->getMockForTrait();

        $result = $this->invokeMethod($trait, 'extractTypesAndNames', $entities);

        $this->assertEquals($expectedResult, $result);
    }
}
