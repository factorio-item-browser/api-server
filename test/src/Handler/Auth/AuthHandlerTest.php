<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Auth;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\Auth\AuthHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $authorizationKey = 'abc';
        $agents = ['def' => 'ghi'];
        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler($authorizationKey, $agents, $modService);
        $this->assertSame($authorizationKey, $this->extractProperty($handler, 'authorizationKey'));
        $this->assertSame($agents, $this->extractProperty($handler, 'agents'));
        $this->assertSame($modService, $this->extractProperty($handler, 'modService'));
    }

    /**
     * Tests the createInputFilter method.
     * @covers ::createInputFilter
     */
    public function testCreateInputFilter(): void
    {
        $expectedFilters = [
            'agent',
            'accessKey',
            'enabledModNames'
        ];

        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler('abc', ['def' => 'ghi'], $modService);

        $result = $this->invokeMethod($handler, 'createInputFilter');
        $this->assertInstanceOf(InputFilter::class, $result);
        /* @var InputFilter $result */
        foreach ($expectedFilters as $filter) {
            $this->assertInstanceOf(InputInterface::class, $result->get($filter));
        }
    }

    /**
     * Provides the data for the handleRequest test.
     * @return array
     */
    public function provideHandleRequest(): array
    {
        $agents = [
            'default' => [
                'accessKey' => 'abc'
            ],
            'demo' => [
                'accessKey' => 'ghi',
                'isDemo' => true
            ]
        ];

        return [
            [
                $agents,
                new DataContainer([
                    'agent' => 'default',
                    'accessKey' => 'abc',
                    'enabledModNames' => ['jkl', 'mno']
                ]),
                false,
                ['jkl', 'mno'],
                'default'
            ],
            [
                $agents,
                new DataContainer([
                    'agent' => 'demo',
                    'accessKey' => 'ghi',
                    'enabledModNames' => ['jkl', 'mno']
                ]),
                false,
                ['base'],
                'demo'
            ],

            [ // Error: Wrong accessKey
                $agents,
                new DataContainer([
                    'agent' => 'default',
                    'accessKey' => 'fail',
                    'enabledModNames' => ['jkl', 'mno']
                ]),
                true,
                [],
                ''
            ],
            [ // Error: Missing accessKey
                $agents,
                new DataContainer([
                    'agent' => 'default',
                    'enabledModNames' => ['jkl', 'mno']
                ]),
                true,
                [],
                ''
            ],
            [ // Error: Unknown agent
                $agents,
                new DataContainer([
                    'agent' => 'unknown',
                    'accessKey' => 'abc',
                    'enabledModNames' => ['jkl', 'mno']
                ]),
                true,
                [],
                ''
            ],
        ];
    }

    /**
     * Tests the handleRequest method.
     * @param array $agents
     * @param DataContainer $requestData
     * @param bool $expectException
     * @param array $expectedEnabledModNames
     * @param string $expectedAgent
     * @covers ::handleRequest
     * @dataProvider provideHandleRequest
     */
    public function testHandleRequest(
        array $agents,
        DataContainer $requestData,
        bool $expectException,
        array $expectedEnabledModNames,
        string $expectedAgent
    ): void {
        if ($expectException) {
            $this->expectException(ApiServerException::class);
            $this->expectExceptionCode(403);
        }

        $modCombinationIds = [1337, 42];
        $expectedResult = [
            'authorizationToken' => 'bar'
        ];

        /* @var ModService|MockObject $modService */
        $modService = $this->getMockBuilder(ModService::class)
                           ->setMethods(['setEnabledCombinationsByModNames', 'getEnabledModCombinationIds'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $modService->expects($expectException ? $this->never() : $this->once())
                   ->method('setEnabledCombinationsByModNames')
                   ->with($expectedEnabledModNames);
        $modService->expects($expectException ? $this->never() : $this->once())
                   ->method('getEnabledModCombinationIds')
                   ->willReturn($modCombinationIds);

        /* @var AuthHandler|MockObject $handler */
        $handler = $this->getMockBuilder(AuthHandler::class)
                        ->setMethods(['createToken'])
                        ->setConstructorArgs(['foo', $agents, $modService])
                        ->getMock();
        $handler->expects($expectException ? $this->never() : $this->once())
                ->method('createToken')
                ->with($expectedAgent, $modCombinationIds)
                ->willReturn('bar');

        $result = $this->invokeMethod($handler, 'handleRequest', $requestData);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the createToken method.
     * @covers ::createToken
     */
    public function testCreateToken(): void
    {
        $agent = 'abc';
        $enabledModCombinationIds = [42, 1337];
        $allowImport = true;

        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new AuthHandler('abc', ['def' => 'ghi'], $modService);
        $this->injectProperty($handler, 'authorizationKey', 'def');

        $result = $this->invokeMethod($handler, 'createToken', $agent, $enabledModCombinationIds, $allowImport);
        $this->assertInternalType('string', $result);
    }
}
