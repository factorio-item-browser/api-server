<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Client\Constant\ServiceName;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\Middleware\RequestDeserializerMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\RequestDeserializerMiddlewareFactory;
use Interop\Container\ContainerInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the RequestDeserializerMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\RequestDeserializerMiddlewareFactory
 */
class RequestDeserializerMiddlewareFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $mapRouteToRequest = ['abc' => 'def'];
        $config = [
            ConfigKey::PROJECT => [
                ConfigKey::API_SERVER => [
                    ConfigKey::MAP_ROUTE_TO_REQUEST => $mapRouteToRequest,
                ],
            ],
        ];

        /* @var SerializerInterface&MockObject $serializer */
        $serializer = $this->createMock(SerializerInterface::class);

        $expectedResult = new RequestDeserializerMiddleware($serializer, $mapRouteToRequest);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [$this->identicalTo('config')],
                      [$this->identicalTo(ServiceName::SERIALIZER)]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $config,
                      $serializer
                  );

        $factory = new RequestDeserializerMiddlewareFactory();
        $result = $factory($container, RequestDeserializerMiddleware::class);

        $this->assertEquals($expectedResult, $result);
    }
}
