<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Middleware\DatabaseConfigurationMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\DatabaseConfigurationMiddlewareFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * The PHPUnit test of the DatabaseConfigurationMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\DatabaseConfigurationMiddlewareFactory
 */
class DatabaseConfigurationMiddlewareFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $config['factorio-item-browser']['api-server']['databaseConnection']['aliases'] = [
            'abc' => 'def'
        ];

        /* @var ServiceManager|MockObject $container */
        $container = $this->getMockBuilder(ServiceManager::class)
                          ->setMethods(['get'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $container->expects($this->once())
                  ->method('get')
                  ->with('config')
                  ->willReturn($config);

        $factory = new DatabaseConfigurationMiddlewareFactory();
        $result = $factory($container, DatabaseConfigurationMiddleware::class);
        $this->assertInstanceOf(DatabaseConfigurationMiddleware::class, $result);
    }
}
