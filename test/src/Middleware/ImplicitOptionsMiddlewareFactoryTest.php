<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Middleware\ImplicitOptionsMiddlewareFactory;
use Interop\Container\ContainerInterface;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;

/**
 * The PHPUnit test of the ImplicitOptionsMiddlewareFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\ImplicitOptionsMiddlewareFactory
 */
class ImplicitOptionsMiddlewareFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $factory = new ImplicitOptionsMiddlewareFactory();
        $result = $factory($container, ImplicitOptionsMiddleware::class);

        $responseFactory = $this->extractProperty($result, 'responseFactory');
        $this->assertIsCallable($responseFactory);

        /* @var ResponseInterface $response */
        $response = $responseFactory();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('3600', $response->getHeaderLine('Access-Control-Max-Age'));
    }
}
