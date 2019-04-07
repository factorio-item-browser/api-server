<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Client\Constant\ServiceName;
use FactorioItemBrowser\Api\Server\Middleware\ResponseSerializerMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\ResponseSerializerMiddlewareFactory;
use Interop\Container\ContainerInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ResponseSerializerMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\ResponseSerializerMiddlewareFactory
 */
class ResponseSerializerMiddlewareFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var SerializerInterface&MockObject $serializer */
        $serializer = $this->createMock(SerializerInterface::class);

        $expectedResult = new ResponseSerializerMiddleware($serializer);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo(ServiceName::SERIALIZER))
                  ->willReturn($serializer);

        $factory = new ResponseSerializerMiddlewareFactory();
        $result = $factory($container, ResponseSerializerMiddleware::class);

        $this->assertEquals($expectedResult, $result);
    }
}
