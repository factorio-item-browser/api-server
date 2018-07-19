<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Middleware\DatabaseConfigurationMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\ServiceManager\ServiceManager;

/**
 * The PHPUnit test of the DatabaseConfigurationMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\DatabaseConfigurationMiddleware
 */
class DatabaseConfigurationMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Provides the data for the process test.
     * @return array
     */
    public function provideProcess(): array
    {
        return [
            ['/import', 'import'],
            ['/foo', 'default']
        ];
    }

    /**
     * Tests the process method.
     * @param string $requestTarget
     * @param string $expectedConfigKey
     * @covers ::process
     * @dataProvider provideProcess
     */
    public function testProcess(string $requestTarget, string $expectedConfigKey)
    {
        /* @var ServerRequest|MockObject $request */
        $request = $this->getMockBuilder(ServerRequest::class)
                        ->setMethods(['getRequestTarget'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $request->expects($this->once())
                ->method('getRequestTarget')
                ->willReturn($requestTarget);

        /* @var Response $response */
        $response = $this->createMock(Response::class);
        /* @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                        ->setMethods(['handle'])
                        ->getMockForAbstractClass();
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

        /* @var DatabaseConfigurationMiddleware|MockObject $middleware */
        $middleware = $this->getMockBuilder(DatabaseConfigurationMiddleware::class)
                           ->setMethods(['injectDatabaseConfiguration'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('injectDatabaseConfiguration')
                   ->with($expectedConfigKey)
                   ->willReturnSelf();

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    /**
     * Tests the injectDatabaseConfiguration method.
     * @covers ::__construct
     * @covers ::injectDatabaseConfiguration
     */
    public function testInjectDatabaseConfiguration()
    {
        $configKey = 'abc';
        $configurationAliases = ['abc' => 'def'];
        $config['doctrine']['connection'] = [
            'def' => ['foo' => 'bar'],
            'ghi' => ['bar' => 'foo']
        ];

        $expectedConfig['doctrine']['connection'] = [
            'def' => ['foo' => 'bar'],
            'ghi' => ['bar' => 'foo'],
            'orm_default' => ['foo' => 'bar']
        ];

        /* @var ServiceManager|MockObject $serviceManager */
        $serviceManager = $this->getMockBuilder(ServiceManager::class)
                               ->setMethods(['get', 'getAllowOverride', 'setAllowOverride', 'setService'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $serviceManager->expects($this->once())
                       ->method('get')
                       ->with('config')
                       ->willReturn($config);
        $serviceManager->expects($this->once())
                       ->method('getAllowOverride')
                       ->willReturn(false);
        $serviceManager->expects($this->exactly(2))
                       ->method('setAllowOverride')
                       ->withConsecutive(
                           [true],
                           [false]
                       );
        $serviceManager->expects($this->once())
                       ->method('setService')
                       ->with('config', $expectedConfig);

        $middleware = new DatabaseConfigurationMiddleware($serviceManager, $configurationAliases);
        $result = $this->invokeMethod($middleware, 'injectDatabaseConfiguration', $configKey);
        $this->assertSame($middleware, $result);
    }
}
