<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationServiceFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AuthorizationServiceFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\AuthorizationServiceFactory
 */
class AuthorizationServiceFactoryTest extends TestCase
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
                        'key' => 'abc'
                    ],
                ],
            ],
        ];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);

        $factory = new AuthorizationServiceFactory();
        $factory($container, AuthorizationService::class);
    }
}
