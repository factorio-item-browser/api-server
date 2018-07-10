<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\ExportData;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\ExportData\ExportDataServiceFactory;
use FactorioItemBrowser\ExportData\Service\ExportDataService;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ExportDataServiceFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\ExportData\ExportDataServiceFactory
 */
class ExportDataServiceFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $directory = 'abc';
        $config['factorio-item-browser']['export-data'] = [
            'directory' => $directory
        ];

        /* @var ExportDataService $exportDataService */
        $exportDataService = $this->createMock(ExportDataService::class);

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with('config')
                  ->willReturn($config);
        
        /* @var ExportDataServiceFactory|MockObject $factory */
        $factory = $this->getMockBuilder(ExportDataServiceFactory::class)
                        ->setMethods(['createInstance'])
                        ->getMock();
        $factory->expects($this->once())
                ->method('createInstance')
                ->with($directory)
                ->willReturn($exportDataService);
        
        $result = $factory($container, ExportDataServiceFactory::class);
        $this->assertSame($exportDataService, $result);
    }

    /**
     * Tests the createInstance method.
     * @covers ::createInstance
     */
    public function testCreateInstance()
    {
        $directory = 'abc';
        $factory = new ExportDataServiceFactory();
        $result = $this->invokeMethod($factory, 'createInstance', $directory);
        $this->assertInstanceOf(ExportDataService::class, $result);
    }
}
