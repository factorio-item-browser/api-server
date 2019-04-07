<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\Middleware\MetaMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\MetaMiddlewareFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MetaMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\MetaMiddlewareFactory
 */
class MetaMiddlewareFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     * @throws ReflectionException
     */
    public function testInvoke(): void
    {
        $config = [
            ConfigKey::PROJECT => [
                ConfigKey::API_SERVER => [
                    ConfigKey::VERSION => '1.2.3',
                ],
            ],
        ];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);

        $factory = new MetaMiddlewareFactory();
        $factory($container, MetaMiddleware::class);
    }
}
