<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Exception\InternalServerException;
use FactorioItemBrowser\Api\Server\Middleware\MatchedRouteNameTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use Zend\Expressive\Router\RouteResult;

/**
 * The PHPUnit test of the MatchedRouteNameTrait class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\MatchedRouteNameTrait
 */
class MatchedRouteNameTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the getMatchedRouteName method.
     * @throws ReflectionException
     * @covers ::getMatchedRouteName
     */
    public function testGetMatchedRouteName(): void
    {
        $matchedRouteName = 'abc';

        /* @var RouteResult&MockObject $routeResult */
        $routeResult = $this->createMock(RouteResult::class);
        $routeResult->expects($this->once())
                    ->method('getMatchedRouteName')
                    ->willReturn($matchedRouteName);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(RouteResult::class))
                ->willReturn($routeResult);

        /* @var MatchedRouteNameTrait&MockObject $trait */
        $trait = $this->getMockBuilder(MatchedRouteNameTrait::class)
                      ->getMockForTrait();

        $result = $this->invokeMethod($trait, 'getMatchedRouteName', $request);

        $this->assertSame($matchedRouteName, $result);
    }

    /**
     * Tests the getMatchedRouteName method.
     * @throws ReflectionException
     * @covers ::getMatchedRouteName
     */
    public function testGetMatchedRouteNameWithoutRouteResult(): void
    {
        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(RouteResult::class))
                ->willReturn(null);

        $this->expectException(InternalServerException::class);

        /* @var MatchedRouteNameTrait&MockObject $trait */
        $trait = $this->getMockBuilder(MatchedRouteNameTrait::class)
                      ->getMockForTrait();

        $this->invokeMethod($trait, 'getMatchedRouteName', $request);
    }
}
