<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Middleware\TranslationMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

/**
 * The PHPUnit test of the TranslationMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Middleware\TranslationMiddleware
 */
class TranslationMiddlewareTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked translation service.
     * @var TranslationService&MockObject
     */
    protected $translationService;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $middleware = new TranslationMiddleware($this->translationService);

        $this->assertSame($this->translationService, $this->extractProperty($middleware, 'translationService'));
    }

    /**
     * Tests the process method.
     * @throws ReflectionException
     * @covers ::process
     */
    public function testProcess(): void
    {
        $locale = 'abc';

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('setLocale')
                           ->with($this->identicalTo($locale));

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        $this->translationService->expects($this->once())
                                 ->method('translate')
                                 ->with($this->identicalTo($authorizationToken));

        /* @var RequestHandlerInterface&MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($request))
                ->willReturn($response);

        /* @var TranslationMiddleware&MockObject $middleware */
        $middleware = $this->getMockBuilder(TranslationMiddleware::class)
                           ->onlyMethods(['getAuthorizationTokenFromRequest', 'getLocaleFromRequest'])
                           ->setConstructorArgs([$this->translationService])
                           ->getMock();
        $middleware->expects($this->once())
                   ->method('getAuthorizationTokenFromRequest')
                   ->with($this->identicalTo($request))
                   ->willReturn($authorizationToken);
        $middleware->expects($this->once())
                   ->method('getLocaleFromRequest')
                   ->with($this->identicalTo($request))
                   ->willReturn($locale);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the getAuthorizationTokenFromRequest method.
     * @throws ReflectionException
     * @covers ::getAuthorizationTokenFromRequest
     */
    public function testGetAuthorizationTokenFromRequest(): void
    {
        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(AuthorizationToken::class))
                ->willReturn($authorizationToken);

        $middleware = new TranslationMiddleware($this->translationService);
        $result = $this->invokeMethod($middleware, 'getAuthorizationTokenFromRequest', $request);

        $this->assertSame($authorizationToken, $result);
    }

    /**
     * Tests the getAuthorizationTokenFromRequest method without an actual token.
     * @throws ReflectionException
     * @covers ::getAuthorizationTokenFromRequest
     */
    public function testGetAuthorizationTokenFromRequestWithoutToken(): void
    {
        $expectedResult = new AuthorizationToken();

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(AuthorizationToken::class))
                ->willReturn(null);

        $middleware = new TranslationMiddleware($this->translationService);
        $result = $this->invokeMethod($middleware, 'getAuthorizationTokenFromRequest', $request);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getLocaleFromRequest method.
     * @throws ReflectionException
     * @covers ::getLocaleFromRequest
     */
    public function testGetLocaleFromRequest(): void
    {
        $acceptLanguage = 'abc';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with($this->identicalTo('Accept-Language'))
                ->willReturn($acceptLanguage);

        $middleware = new TranslationMiddleware($this->translationService);
        $result = $this->invokeMethod($middleware, 'getLocaleFromRequest', $request);

        $this->assertSame($acceptLanguage, $result);
    }

    /**
     * Tests the getLocaleFromRequest method without an actual header value.
     * @throws ReflectionException
     * @covers ::getLocaleFromRequest
     */
    public function testGetLocaleFromRequestWithoutHeader(): void
    {
        $acceptLanguage = '';
        $expectedResult = Config::DEFAULT_LOCALE;

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getHeaderLine')
                ->with($this->identicalTo('Accept-Language'))
                ->willReturn($acceptLanguage);

        $middleware = new TranslationMiddleware($this->translationService);
        $result = $this->invokeMethod($middleware, 'getLocaleFromRequest', $request);

        $this->assertSame($expectedResult, $result);
    }
}
