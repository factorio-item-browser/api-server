<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Response;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Constant\ServiceName;
use FactorioItemBrowser\Api\Server\Response\ErrorResponseGenerator;
use FactorioItemBrowser\Api\Server\Response\ErrorResponseGeneratorFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\Log\LoggerInterface;

/**
 * The PHPUnit test of the ErrorResponseGeneratorFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Response\ErrorResponseGeneratorFactory
 */
class ErrorResponseGeneratorFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $config = [
            'debug' => true,
        ];
        /* @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        $expectedResult = new ErrorResponseGenerator($logger, true);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);

        /* @var ErrorResponseGeneratorFactory&MockObject $factory */
        $factory = $this->getMockBuilder(ErrorResponseGeneratorFactory::class)
                        ->onlyMethods(['fetchLogger'])
                        ->getMock();
        $factory->expects($this->once())
                ->method('fetchLogger')
                ->with($this->identicalTo($container))
                ->willReturn($logger);

        $result = $factory($container, ErrorResponseGenerator::class);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the fetchLogger method.
     * @throws ReflectionException
     * @covers ::fetchLogger
     */
    public function testFetchLogger(): void
    {
        /* @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('has')
                  ->with($this->identicalTo(ServiceName::LOGGER))
                  ->willReturn(true);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo(ServiceName::LOGGER))
                  ->willReturn($logger);

        $factory = new ErrorResponseGeneratorFactory();
        $result = $this->invokeMethod($factory, 'fetchLogger', $container);

        $this->assertSame($logger, $result);
    }

    /**
     * Tests the fetchLogger method without an actual logger.
     * @throws ReflectionException
     * @covers ::fetchLogger
     */
    public function testFetchLoggerWithoutLogger(): void
    {
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('has')
                  ->with($this->identicalTo(ServiceName::LOGGER))
                  ->willReturn(false);
        $container->expects($this->never())
                  ->method('get');

        $factory = new ErrorResponseGeneratorFactory();
        $result = $this->invokeMethod($factory, 'fetchLogger', $container);

        $this->assertNull($result);
    }
}
