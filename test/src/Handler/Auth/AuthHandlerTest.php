<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Auth;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\UnknownAgentException;
use FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputInterface;

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
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var AgentService|MockObject $agentService */
        $agentService = $this->createMock(AgentService::class);
        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);
        /* @var ModService|MockObject $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler($agentService, $authorizationService, $modService);

        $this->assertSame($agentService, $this->extractProperty($handler, 'agentService'));
        $this->assertSame($authorizationService, $this->extractProperty($handler, 'authorizationService'));
        $this->assertSame($modService, $this->extractProperty($handler, 'modService'));
    }

    /**
     * Tests the createInputFilter method.
     * @throws ReflectionException
     * @covers ::createInputFilter
     */
    public function testCreateInputFilter(): void
    {
        $expectedFilters = [
            'agent',
            'accessKey',
            'enabledModNames'
        ];

        /* @var AgentService|MockObject $agentService */
        $agentService = $this->createMock(AgentService::class);
        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);
        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler($agentService, $authorizationService, $modService);
        $result = $this->invokeMethod($handler, 'createInputFilter');

        /* @var InputFilter $result */
        foreach ($expectedFilters as $filter) {
            $this->assertInstanceOf(InputInterface::class, $result->get($filter));
        }
    }

    /**
     * Tests the handleRequest method.
     * @throws ReflectionException
     * @covers ::handleRequest
     */
    public function testHandleRequest(): void
    {
        $enabledModCombinationIds = [42, 1337];
        $serializedToken = 'abc';
        $expectedResult = ['authorizationToken' => $serializedToken];
        
        /* @var DataContainer|MockObject $requestData */
        $requestData = $this->createMock(DataContainer::class);
        /* @var Agent|MockObject $agent */
        $agent = $this->createMock(Agent::class);
        /* @var AuthorizationToken|MockObject $token */
        $token = $this->createMock(AuthorizationToken::class);
        
        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);
        $authorizationService->expects($this->once())
                             ->method('createToken')
                             ->with($this->identicalTo($agent), $this->identicalTo($enabledModCombinationIds))
                             ->willReturn($token);
        $authorizationService->expects($this->once())
                             ->method('serializeToken')
                             ->with($this->identicalTo($token))
                             ->willReturn($serializedToken);
        
        /* @var AgentService|MockObject $agentService */
        $agentService = $this->createMock(AgentService::class);
        /* @var ModService|MockObject $modService */
        $modService = $this->createMock(ModService::class);
        
        /* @var AuthHandler|MockObject $handler */
        $handler = $this->getMockBuilder(AuthHandler::class)
                        ->setMethods(['getAgentFromRequestData', 'getEnabledModCombinationIdsFromRequestData'])
                        ->setConstructorArgs([$agentService, $authorizationService, $modService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAgentFromRequestData')
                ->with($this->identicalTo($requestData))
                ->willReturn($agent);
        $handler->expects($this->once())
                ->method('getEnabledModCombinationIdsFromRequestData')
                ->with($this->identicalTo($agent), $this->identicalTo($requestData))
                ->willReturn($enabledModCombinationIds);

        $result = $this->invokeMethod($handler, 'handleRequest', $requestData);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the getAgentFromRequestData test.
     * @return array
     */
    public function provideGetAgentFromRequestData(): array
    {
        $agent = (new Agent())->setName('foo');

        return [
            [$agent, false],
            [null, true],
        ];
    }

    /**
     * Tests the getAgentFromRequestData method.
     * @param Agent|null $agent
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::getAgentFromRequestData
     * @dataProvider provideGetAgentFromRequestData
     */
    public function testGetAgentFromRequestData(?Agent $agent, bool $expectException): void
    {
        $agentName = 'abc';
        $accessKey = 'def';
        $requestData = new DataContainer([
            'agent' => $agentName,
            'accessKey' => $accessKey,
        ]);

        /* @var AgentService|MockObject $agentService */
        $agentService = $this->createMock(AgentService::class);
        $agentService->expects($this->once())
                     ->method('getByAccessKey')
                     ->with($this->identicalTo($agentName), $this->identicalTo($accessKey))
                     ->willReturn($agent);

        if ($expectException) {
            $this->expectException(UnknownAgentException::class);
        }

        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);
        /* @var ModService|MockObject $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler($agentService, $authorizationService, $modService);
        $result = $this->invokeMethod($handler, 'getAgentFromRequestData', $requestData);

        $this->assertEquals($agent, $result);
    }

    /**
     * Provides the data for the getEnabledModCombinationIdsFromRequestData test.
     * @return array
     */
    public function provideGetEnabledModCombinationIdsFromRequestData(): array
    {
        return [
            [false, ['abc', 'def'], ['abc', 'def']],
            [true, ['abc', 'def'], ['base']],
        ];
    }

    /**
     * Tests the getEnabledModCombinationIdsFromRequestData method.
     * @param bool $isDemo
     * @param array $enabledModNames
     * @param array $expectedModNames
     * @throws ReflectionException
     * @covers ::getEnabledModCombinationIdsFromRequestData
     * @dataProvider provideGetEnabledModCombinationIdsFromRequestData
     */
    public function testGetEnabledModCombinationIdsFromRequestData(
        bool $isDemo,
        array $enabledModNames,
        array $expectedModNames
    ): void {
        $agent = new Agent();
        $agent->setIsDemo($isDemo);
        $requestData = new DataContainer(['enabledModNames' => $enabledModNames]);
        $enabledModCombinationIds = [42, 1337];

        /* @var ModService|MockObject $modService */
        $modService = $this->createMock(ModService::class);
        $modService->expects($this->once())
                   ->method('setEnabledCombinationsByModNames')
                   ->with($this->identicalTo($expectedModNames));
        $modService->expects($this->once())
                   ->method('getEnabledModCombinationIds')
                   ->willReturn($enabledModCombinationIds);

        /* @var AgentService|MockObject $agentService */
        $agentService = $this->createMock(AgentService::class);
        /* @var AuthorizationService|MockObject $authorizationService */
        $authorizationService = $this->createMock(AuthorizationService::class);

        $handler = new AuthHandler($agentService, $authorizationService, $modService);
        $result = $this->invokeMethod($handler, 'getEnabledModCombinationIdsFromRequestData', $agent, $requestData);

        $this->assertSame($enabledModCombinationIds, $result);
    }
}
