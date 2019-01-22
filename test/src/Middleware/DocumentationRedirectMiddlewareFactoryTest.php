<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use Blast\BaseUrl\BasePathHelper;
use FactorioItemBrowser\Api\Server\Middleware\DocumentationRedirectMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\DocumentationRedirectMiddlewareFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DocumentationRedirectMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\DocumentationRedirectMiddlewareFactory
 */
class DocumentationRedirectMiddlewareFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with(BasePathHelper::class)
                  ->willReturn($this->createMock(BasePathHelper::class));

        $factory = new DocumentationRedirectMiddlewareFactory();
        $factory($container, DocumentationRedirectMiddleware::class);
    }
}
