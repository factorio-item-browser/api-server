<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Auth;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Request\Auth\AuthRequest;
use FactorioItemBrowser\Api\Client\Response\Auth\AuthResponse;
use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\UnknownAgentException;
use FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use FactorioItemBrowser\Common\Constant\Constant;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->agentService = $this->createMock(AgentService::class);
        $this->authorizationService = $this->createMock(AuthorizationService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new AuthHandler($this->agentService, $this->authorizationService);

        $this->assertSame($this->agentService, $this->extractProperty($handler, 'agentService'));
        $this->assertSame($this->authorizationService, $this->extractProperty($handler, 'authorizationService'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = AuthRequest::class;

        $handler = new AuthHandler($this->agentService, $this->authorizationService);
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
        /* @var AuthorizationToken&MockObject $token */
        $token = $this->createMock(AuthorizationToken::class);
        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);
        /* @var AuthResponse&MockObject $response */
        $response = $this->createMock(AuthResponse::class);

        /* @var AuthHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AuthHandler::class)
                        ->onlyMethods(['createAuthorizationToken', 'createResponse'])
                        ->setConstructorArgs([$this->agentService, $this->authorizationService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('createAuthorizationToken')
                ->with($this->identicalTo($request))
                ->willReturn($token);
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->identicalTo($token))
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the createAuthorizationToken method.
     * @throws ReflectionException
     * @covers ::createAuthorizationToken
     */
    public function testCreateAuthorizationToken(): void
    {
        $agentName = 'abc';
        $modNames = ['def', 'ghi'];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);

        /* @var Agent&MockObject $agent */
        $agent = $this->createMock(Agent::class);
        $agent->expects($this->once())
              ->method('getName')
              ->willReturn($agentName);

        $expectedResult = new AuthorizationToken();
        $expectedResult->setAgentName($agentName)
                       ->setCombinationId($combinationId)
                       ->setModNames($modNames);

        /* @var AuthHandler&MockObject $handler */
        $handler = $this->getMockBuilder(AuthHandler::class)
                        ->onlyMethods(['getAgentFromRequest', 'getModNamesFromRequest', 'calculateCombinationId'])
                        ->setConstructorArgs([$this->agentService, $this->authorizationService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAgentFromRequest')
                ->with($this->identicalTo($request))
                ->willReturn($agent);
        $handler->expects($this->once())
                ->method('getModNamesFromRequest')
                ->with($this->identicalTo($agent), $this->identicalTo($request))
                ->willReturn($modNames);
        $handler->expects($this->once())
                ->method('calculateCombinationId')
                ->with($this->identicalTo($modNames))
                ->willReturn($combinationId);

        $result = $this->invokeMethod($handler, 'createAuthorizationToken', $request);

        $this->assertEquals($expectedResult, $result);
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

        $handler = new AuthHandler($this->agentService, $this->authorizationService);
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

        $handler = new AuthHandler($this->agentService, $this->authorizationService);
        $this->invokeMethod($handler, 'getAgentFromRequest', $request);
    }

    /**
     * Tests the getModNamesFromRequest method.
     * @throws ReflectionException
     * @covers ::getModNamesFromRequest
     */
    public function testGetModNamesFromRequest(): void
    {
        $isDemo = false;
        $modNames = ['def', 'ghi'];

        /* @var Agent&MockObject $agent */
        $agent = $this->createMock(Agent::class);
        $agent->expects($this->once())
              ->method('getIsDemo')
              ->willReturn($isDemo);

        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);
        $request->expects($this->once())
                ->method('getEnabledModNames')
                ->willReturn($modNames);

        $handler = new AuthHandler($this->agentService, $this->authorizationService);
        $result = $this->invokeMethod($handler, 'getModNamesFromRequest', $agent, $request);

        $this->assertSame($modNames, $result);
    }

    /**
     * Tests the getModNamesFromRequest method.
     * @throws ReflectionException
     * @covers ::getModNamesFromRequest
     */
    public function testGetModNamesFromRequestWithDemoAgent(): void
    {
        $isDemo = true;
        $expectedResult = [Constant::MOD_NAME_BASE];

        /* @var Agent&MockObject $agent */
        $agent = $this->createMock(Agent::class);
        $agent->expects($this->once())
              ->method('getIsDemo')
              ->willReturn($isDemo);

        /* @var AuthRequest&MockObject $request */
        $request = $this->createMock(AuthRequest::class);
        $request->expects($this->never())
                ->method('getEnabledModNames');

        $handler = new AuthHandler($this->agentService, $this->authorizationService);
        $result = $this->invokeMethod($handler, 'getModNamesFromRequest', $agent, $request);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the calculateCombinationId test.
     * @return array
     */
    public function provideCalculateCombinationId(): array
    {
        return [
            [[Constant::MOD_NAME_BASE], Uuid::fromString(Config::DEFAULT_COMBINATION_ID)],
            [['abc', 'def'], Uuid::fromString('9e86daa1-e1bd-94ed-176d-afd437e13d58')],
            [[' def', 'abc '], Uuid::fromString('9e86daa1-e1bd-94ed-176d-afd437e13d58')],
        ];
    }

    /**
     * Tests the calculateCombinationId method.
     * @param array|string[] $modNames
     * @param UuidInterface $expectedResult
     * @throws ReflectionException
     * @covers ::calculateCombinationId
     * @dataProvider provideCalculateCombinationId
     */
    public function testCalculateCombinationId(array $modNames, UuidInterface $expectedResult): void
    {
        $handler = new AuthHandler($this->agentService, $this->authorizationService);
        $result = $this->invokeMethod($handler, 'calculateCombinationId', $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        $serializedToken = 'abc';

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);

        $expectedResult = new AuthResponse();
        $expectedResult->setAuthorizationToken($serializedToken);

        $this->authorizationService->expects($this->once())
                                   ->method('serializeToken')
                                   ->with($this->identicalTo($authorizationToken))
                                   ->willReturn($serializedToken);

        $handler = new AuthHandler($this->agentService, $this->authorizationService);
        $result = $this->invokeMethod($handler, 'createResponse', $authorizationToken);

        $this->assertEquals($expectedResult, $result);
    }
}
