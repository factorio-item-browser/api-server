<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\AgentServiceFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AgentServiceFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\AgentServiceFactory
 */
class AgentServiceFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $config = [
            'factorio-item-browser' => [
                'api-server' => [
                    'agents' => [
                        'abc' => ['def' => 'ghi'],
                        'jkl' => ['mno' => 'pqr'],
                    ],
                ],
            ],
        ];

        /* @var ContainerInterface|MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);

        /* @var AgentServiceFactory|MockObject $factory */
        $factory = $this->getMockBuilder(AgentServiceFactory::class)
                        ->setMethods(['createAgent'])
                        ->getMock();
        $factory->expects($this->exactly(2))
                ->method('createAgent')
                ->withConsecutive(
                    [$this->identicalTo('abc'), $this->identicalTo(['def' => 'ghi'])],
                    [$this->identicalTo('jkl'), $this->identicalTo(['mno' => 'pqr'])]
                )
                ->willReturnOnConsecutiveCalls(
                    $this->createMock(Agent::class),
                    $this->createMock(Agent::class)
                );

        $factory($container, AgentService::class);
    }

    /**
     * Provides the data for the createAgent test.
     * @return array
     */
    public function provideCreateAgent(): array
    {
        $agent1 = new Agent();
        $agent1->setName('abc')
               ->setAccessKey('def')
               ->setIsDemo(true);

        $agent2 = new Agent();
        $agent2->setName('abc')
               ->setAccessKey('')
               ->setIsDemo(false);

        return [
            ['abc', ['access-key' => 'def', 'demo' => true], $agent1],
            ['abc', [], $agent2],
        ];
    }

    /**
     * Tests the createAgent method.
     * @param string $name
     * @param array $agentConfig
     * @param Agent $expectedResult
     * @throws ReflectionException
     * @covers ::createAgent
     * @dataProvider provideCreateAgent
     */
    public function testCreateAgent(string $name, array $agentConfig, Agent $expectedResult): void
    {
        $factory = new AgentServiceFactory();
        $result = $this->invokeMethod($factory, 'createAgent', $name, $agentConfig);

        $this->assertEquals($expectedResult, $result);
    }
}
