<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
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
        $agents = [
            $this->createMock(Agent::class),
            $this->createMock(Agent::class),
        ];
        $service = new AgentService($agents);

        $this->assertSame($agents, $this->extractProperty($service, 'agents'));
    }

    /**
     * Provides the data for the getByAccessKey test.
     * @return array<mixed>
     */
    public function provideGetByAccessKey(): array
    {
        $agent1 = new Agent();
        $agent1->setAccessKey('abc');
        $agent2 = new Agent();
        $agent2->setAccessKey('def');
        $agents = [$agent1, $agent2];

        return [
            [$agents, 'abc', $agent1],
            [$agents, 'foo', null],
            [$agents, '', null],
        ];
    }

    /**
     * Tests the getByAccessKey method.
     * @param array|Agent[] $agents
     * @param string $accessKey
     * @param Agent|null $expectedResult
     * @covers ::getByAccessKey
     * @dataProvider provideGetByAccessKey
     */
    public function testGetByAccessKey(array $agents, string $accessKey, ?Agent $expectedResult): void
    {
        $service = new AgentService($agents);
        $result = $service->getByAccessKey($accessKey);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Provides the data for the getByName test.
     * @return array<mixed>
     */
    public function provideGetByName(): array
    {
        $agent1 = new Agent();
        $agent1->setName('abc');
        $agent2 = new Agent();
        $agent2->setName('def');
        $agents = [$agent1, $agent2];

        return [
            [$agents, 'abc', $agent1],
            [$agents, 'foo', null],
            [$agents, '', null],
        ];
    }

    /**
     * Tests the getByName method.
     * @param array|Agent[] $agents
     * @param string $name
     * @param Agent|null $expectedResult
     * @covers ::getByName
     * @dataProvider provideGetByName
     */
    public function testGetByName(array $agents, string $name, ?Agent $expectedResult): void
    {
        $service = new AgentService($agents);
        $result = $service->getByName($name);

        $this->assertSame($expectedResult, $result);
    }
}
