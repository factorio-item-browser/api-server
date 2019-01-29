<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AgentService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\AgentService
 */
class AgentServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $agent1 = new Agent();
        $agent1->setName('abc');
        $agent2 = new Agent();
        $agent2->setName('def');
        $expectedAgents = ['abc' => $agent1, 'def' => $agent2];

        $service = new AgentService([$agent1, $agent2]);

        $this->assertSame($expectedAgents, $this->extractProperty($service, 'agents'));
    }

    /**
     * Provides the data for the getByAccessKey test.
     * @return array
     */
    public function provideGetByAccessKey(): array
    {
        $agent1 = new Agent();
        $agent1->setName('abc')
               ->setAccessKey('def');
        $agent2 = new Agent();
        $agent2->setName('ghi')
               ->setAccessKey('jkl');
        $agents = [
            'abc' => $agent1,
            'ghi' => $agent2,
        ];

        return [
            [$agents, 'abc', 'def', $agent1],
            [$agents, 'ghi', 'foo', null],
            [$agents, 'foo', 'bar', null],
        ];
    }

    /**
     * Tests the getByAccessKey method.
     * @param array|Agent[] $agents
     * @param string $name
     * @param string $accessKey
     * @param Agent|null $expectedResult
     * @covers ::getByAccessKey
     * @dataProvider provideGetByAccessKey
     */
    public function testGetByAccessKey(array $agents, string $name, string $accessKey, ?Agent $expectedResult): void
    {
        $service = new AgentService($agents);
        $result = $service->getByAccessKey($name, $accessKey);

        $this->assertSame($expectedResult, $result);
    }
}
