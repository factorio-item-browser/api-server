<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddlewareFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AuthorizationMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddlewareFactory
 */
class AuthorizationMiddlewareFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $config = [
            'factorio-item-browser' => [
                'api-server' => [
                    'authorization' => [
                        'key' => 'abc',
                    ],
                ],
            ],
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

        $factory = new AuthorizationMiddlewareFactory();
        $factory($container, AuthorizationMiddleware::class);
    }
}
