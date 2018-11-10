<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Service\AbstractDatabaseServiceFactory;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AbstractDatabaseServiceFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Service\AbstractDatabaseServiceFactory
 */
class AbstractDatabaseServiceFactoryTest extends TestCase
{
    /**
     * Provides the data for the invoke test.
     * @return array
     */
    public function provideInvoke(): array
    {
        return [
            [ModService::class],
        ];
    }

    /**
     * Tests the invoking.
     * @param string $className
     * @covers ::__invoke
     * @dataProvider provideInvoke
     */
    public function testInvoke(string $className)
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with(EntityManager::class)
                  ->willReturn($this->createMock(EntityManager::class));

        $factory = new AbstractDatabaseServiceFactory();
        $result = $factory($container, $className);
        $this->assertInstanceOf($className, $result);
    }
}
