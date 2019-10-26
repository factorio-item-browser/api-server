<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Auth;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Request\Auth\AuthRequest;
use FactorioItemBrowser\Api\Client\Response\Auth\AuthResponse;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\UnknownAgentException;
use FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler;
use FactorioItemBrowser\Api\Server\ModResolver\ModCombinationResolver;
use FactorioItemBrowser\Api\Server\ModResolver\ModDependencyResolver;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AuthHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler
 */
class AuthHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked agent service.
     * @var AgentService&MockObject
     */
    protected $agentService;

    /**
     * The mocked authorization service.
     * @var AuthorizationService&MockObject
     */
    protected $authorizationService;

    /**
     * The mocked mod combination resolver.
     * @var ModCombinationResolver&MockObject
     */
    protected $modCombinationResolver;

    /**
     * The mocked mod dependency resolver.
     * @var ModDependencyResolver&MockObject
     */
    protected $modDependencyResolver;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->agentService = $this->createMock(AgentService::class);
        $this->authorizationService = $this->createMock(AuthorizationService::class);
        $this->modCombinationResolver = $this->createMock(ModCombinationResolver::class);
        $this->modDependencyResolver = $this->createMock(ModDependencyResolver::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new AuthHandler(
            $this->agentService,
            $this->authorizationService,
            $this->modCombinationResolver,
            $this->modDependencyResolver
        );

        $this->assertSame($this->agentService, $this->extractProperty($handler, 'agentService'));
        $this->assertSame($this->authorizationService, $this->extractProperty($handler, 'authorizationService'));
        $this->assertSame($this->modCombinationResolver, $this->extractProperty($handler, 'modCombinationResolver'));
        $this->assertSame($this->modDependencyResolver, $this->extractProperty($handler, 'modDependencyResolver'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = AuthRequest::class;

        $handler = new AuthHandler(
            $this->agentService,
            $this->authorizationService,
            $this->modCombinationResolver,
            $this->modDependencyResolver
        );
        $result = $this->invokeMethod($handler, 'getExpectedRequestClass');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the handleRequest method.
     * @throws ReflectionException
     * @covers ::handleRequest
     */
    public function testHandleRequest(): void
    {
        $enabledModCombinationIds = [42, 1337];
        $authorizationToken = 'abc';

        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);
        /* @var Agent&MockObject $agent */
        $agent = $this->createMock(Agent::class);
        /* @var AuthResponse&MockObject $response */
        $response = $this->createMock(AuthResponse::class);

        /* @var AuthHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AuthHandler::class)
                        ->onlyMethods([
                            'getAgentFromRequest',
                            'getEnabledModCombinationIdsFromRequest',
                            'createAuthorizationToken',
                            'createResponse',
                        ])
                        ->setConstructorArgs([
                            $this->agentService,
                            $this->authorizationService,
                            $this->modCombinationResolver,
                            $this->modDependencyResolver,
                        ])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAgentFromRequest')
                ->with($this->identicalTo($request))
                ->willReturn($agent);
        $handler->expects($this->once())
                ->method('getEnabledModCombinationIdsFromRequest')
                ->with($this->identicalTo($agent), $this->identicalTo($request))
                ->willReturn($enabledModCombinationIds);
        $handler->expects($this->once())
                ->method('createAuthorizationToken')
                ->with($this->identicalTo($agent), $this->identicalTo($enabledModCombinationIds))
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->identicalTo($authorizationToken))
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the getAgentFromRequest method.
     * @throws ReflectionException
     * @covers ::getAgentFromRequest
     */
    public function testGetAgentFromRequest(): void
    {
        $agentName = 'abc';
        $accessKey = 'def';

        /* @var Agent&MockObject $agent */
        $agent = $this->createMock(Agent::class);

        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);
        $request->expects($this->once())
                ->method('getAgent')
                ->willReturn($agentName);
        $request->expects($this->once())
                ->method('getAccessKey')
                ->willReturn($accessKey);

        $this->agentService->expects($this->once())
                           ->method('getByAccessKey')
                           ->with($this->identicalTo($agentName), $this->identicalTo($accessKey))
                           ->willReturn($agent);

        $handler = new AuthHandler(
            $this->agentService,
            $this->authorizationService,
            $this->modCombinationResolver,
            $this->modDependencyResolver
        );
        $result = $this->invokeMethod($handler, 'getAgentFromRequest', $request);

        $this->assertSame($agent, $result);
    }

    /**
     * Tests the getAgentFromRequest method.
     * @throws ReflectionException
     * @covers ::getAgentFromRequest
     */
    public function testGetAgentFromRequestWithException(): void
    {
        $agentName = 'abc';
        $accessKey = 'def';

        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);
        $request->expects($this->once())
                ->method('getAgent')
                ->willReturn($agentName);
        $request->expects($this->once())
                ->method('getAccessKey')
                ->willReturn($accessKey);

        $this->agentService->expects($this->once())
                           ->method('getByAccessKey')
                           ->with($this->identicalTo($agentName), $this->identicalTo($accessKey))
                           ->willReturn(null);

        $this->expectException(UnknownAgentException::class);

        $handler = new AuthHandler(
            $this->agentService,
            $this->authorizationService,
            $this->modCombinationResolver,
            $this->modDependencyResolver
        );
        $this->invokeMethod($handler, 'getAgentFromRequest', $request);
    }
    
    /**
     * Tests the getEnabledModCombinationIdsFromRequest method.
     * @throws ReflectionException
     * @covers ::getModModNamesFromRequest
     */
    public function testGetEnabledModCombinationIdsFromRequest(): void
    {
        $isDemo = false;
        $enabledModNames = ['abc', 'def'];
        $modNames = ['ghi', 'jkl'];
        $enabledModCombinationIds = [42, 1337];
        
        /* @var Agent&MockObject $agent */
        $agent = $this->createMock(Agent::class);
        $agent->expects($this->once())
              ->method('getIsDemo')
              ->willReturn($isDemo);
        
        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);
        $request->expects($this->once())
                ->method('getEnabledModNames')
                ->willReturn($enabledModNames);

        $this->modDependencyResolver->expects($this->once())
                                    ->method('resolve')
                                    ->with($this->identicalTo($enabledModNames))
                                    ->willReturn($modNames);

        $this->modCombinationResolver->expects($this->once())
                                     ->method('resolve')
                                     ->with($this->identicalTo($modNames))
                                     ->willReturn($enabledModCombinationIds);

        $handler = new AuthHandler(
            $this->agentService,
            $this->authorizationService,
            $this->modCombinationResolver,
            $this->modDependencyResolver
        );
        $result = $this->invokeMethod($handler, 'getEnabledModCombinationIdsFromRequest', $agent, $request);
        
        $this->assertSame($enabledModCombinationIds, $result);
    }

    /**
     * Tests the getEnabledModCombinationIdsFromRequest method with the demo agent.
     * @throws ReflectionException
     * @covers ::getModModNamesFromRequest
     */
    public function testGetEnabledModCombinationIdsFromRequestWithDemoAgent(): void
    {
        $isDemo = true;
        $expectedModNames = ['base'];
        $enabledModCombinationIds = [42, 1337];

        /* @var Agent&MockObject $agent */
        $agent = $this->createMock(Agent::class);
        $agent->expects($this->once())
              ->method('getIsDemo')
              ->willReturn($isDemo);

        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);

        $this->modCombinationResolver->expects($this->once())
                                     ->method('resolve')
                                     ->with($this->identicalTo($expectedModNames))
                                     ->willReturn($enabledModCombinationIds);

        $handler = new AuthHandler(
            $this->agentService,
            $this->authorizationService,
            $this->modCombinationResolver,
            $this->modDependencyResolver
        );
        $result = $this->invokeMethod($handler, 'getEnabledModCombinationIdsFromRequest', $agent, $request);

        $this->assertSame($enabledModCombinationIds, $result);
    }

    /**
     * Tests the createAuthorizationToken method.
     * @throws ReflectionException
     * @covers ::createAuthorizationToken
     */
    public function testCreateAuthorizationToken(): void
    {
        $enabledModCombinationIds = [42, 1337];
        $serializedToken = 'abc';

        /* @var Agent&MockObject $agent */
        $agent = $this->createMock(Agent::class);
        /* @var AuthorizationToken&MockObject $token */
        $token = $this->createMock(AuthorizationToken::class);

        $this->authorizationService->expects($this->once())
                                   ->method('createToken')
                                   ->with($this->identicalTo($agent), $this->identicalTo($enabledModCombinationIds))
                                   ->willReturn($token);
        $this->authorizationService->expects($this->once())
                                   ->method('serializeToken')
                                   ->with($this->identicalTo($token))
                                   ->willReturn($serializedToken);

        $handler = new AuthHandler(
            $this->agentService,
            $this->authorizationService,
            $this->modCombinationResolver,
            $this->modDependencyResolver
        );
        $result = $this->invokeMethod($handler, 'createAuthorizationToken', $agent, $enabledModCombinationIds);

        $this->assertSame($serializedToken, $result);
    }

    /**
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        $authorizationToken = 'abc';

        $expectedResult = new AuthResponse();
        $expectedResult->setAuthorizationToken($authorizationToken);

        $handler = new AuthHandler(
            $this->agentService,
            $this->authorizationService,
            $this->modCombinationResolver,
            $this->modDependencyResolver
        );
        $result = $this->invokeMethod($handler, 'createResponse', $authorizationToken);

        $this->assertEquals($expectedResult, $result);
    }
}
