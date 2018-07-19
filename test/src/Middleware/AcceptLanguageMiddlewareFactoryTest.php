<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Middleware\AcceptLanguageMiddleware;
use FactorioItemBrowser\Api\Server\Middleware\AcceptLanguageMiddlewareFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AcceptLanguageMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\AcceptLanguageMiddlewareFactory
 */
class AcceptLanguageMiddlewareFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with(TranslationService::class)
                  ->willReturn($this->createMock(TranslationService::class));

        $factory = new AcceptLanguageMiddlewareFactory();
        $result = $factory($container, AcceptLanguageMiddleware::class);
        $this->assertInstanceOf(AcceptLanguageMiddleware::class, $result);
    }
}
