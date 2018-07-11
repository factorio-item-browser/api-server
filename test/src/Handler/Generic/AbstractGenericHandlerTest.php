<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Handler\Generic\AbstractGenericHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputInterface;

/**
 * The PHPUnit test of the AbstractGenericHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Generic\AbstractGenericHandler
 */
class AbstractGenericHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the createInputFilter method.
     * @covers ::createInputFilter
     */
    public function testCreateInputFilter()
    {
        $expectedEntitiesFilters = [
            'type',
            'name',
        ];


        /* @var AbstractGenericHandler|MockObject $handler */
        $handler = $this->getMockBuilder(AbstractGenericHandler::class)
                        ->getMockForAbstractClass();

        /* @var InputFilter $result */
        $result = $this->invokeMethod($handler, 'createInputFilter');
        $this->assertInstanceOf(InputFilter::class, $result);

        /* @var CollectionInputFilter $entitiesFilter */
        $entitiesFilter = $result->get('entities');
        $this->assertInstanceOf(CollectionInputFilter::class, $entitiesFilter);
        /* @var InputFilter $result */
        foreach ($expectedEntitiesFilters as $filter) {
            $this->assertInstanceOf(InputInterface::class, $entitiesFilter->getInputFilter()->get($filter));
        }
    }

    /**
     * Tests the getEntityNamesByType method.
     * @covers ::getEntityNamesByType
     */
    public function testGetEntityNamesByType()
    {
        $requestData = new DataContainer([
            'entities' => [
                [
                    'type' => 'abc',
                    'name' => 'def'
                ],
                [
                    'type' => 'abc',
                    'name' => 'ghi'
                ],
                [
                    'type' => 'jkl',
                    'name' => 'mno'
                ],
            ]
        ]);
        $expectedResult = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno']
        ];

        /* @var AbstractGenericHandler|MockObject $handler */
        $handler = $this->getMockBuilder(AbstractGenericHandler::class)
                        ->getMockForAbstractClass();

        $result = $this->invokeMethod($handler, 'getEntityNamesByType', $requestData);
        $this->assertSame($expectedResult, $result);
    }
}
