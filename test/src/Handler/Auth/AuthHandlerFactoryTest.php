<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Auth;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler;
use FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandlerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AuthHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandlerFactory
 */
class AuthHandlerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $config['factorio-item-browser']['api-server']['authorization'] = [
            'key' => 'abc',
            'agents' => ['def' => 'ghi']
        ];

        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      ['config'],
                      [ModService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $config,
                      $this->createMock(ModService::class)
                  );

        $factory = new AuthHandlerFactory();
        $result = $factory($container, AuthHandler::class);
        $this->assertInstanceOf(AuthHandler::class, $result);
    }
}
