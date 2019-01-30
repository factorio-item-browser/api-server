<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\AuthorizationMiddlewareFactory;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
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
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [AuthorizationService::class],
                      [ModService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(AuthorizationService::class),
                      $this->createMock(ModService::class)
                  );

        $factory = new AuthorizationMiddlewareFactory();
        $factory($container, AuthorizationMiddleware::class);
    }
}
