<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Entity;

use FactorioItemBrowser\Api\Server\Entity\Agent;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Agent class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Entity\Agent
 */
class AgentTest extends TestCase
{
    /**
     * Tests the constructing.
     * @coversNothing
     */
    public function testConstruct(): void
    {
        $agent = new Agent();

        $this->assertSame('', $agent->getName());
        $this->assertSame('', $agent->getAccessKey());
        $this->assertFalse($agent->getIsDemo());
    }

    /**
     * Tests setting and getting the name.
     * @covers ::getName
     * @covers ::setName
     */
    public function testSetAndGetName(): void
    {
        $name = 'abc';
        $agent = new Agent();

        $this->assertSame($agent, $agent->setName($name));
        $this->assertSame($name, $agent->getName());
    }

    /**
     * Tests setting and getting the access key.
     * @covers ::getAccessKey
     * @covers ::setAccessKey
     */
    public function testSetAndGetAccessKey(): void
    {
        $accessKey = 'abc';
        $agent = new Agent();

        $this->assertSame($agent, $agent->setAccessKey($accessKey));
        $this->assertSame($accessKey, $agent->getAccessKey());
    }

    /**
     * Tests setting and getting the demo flag.
     * @covers ::getIsDemo
     * @covers ::setIsDemo
     */
    public function testSetAndGetIsDemo(): void
    {
        $isDemo = true;
        $agent = new Agent();

        $this->assertSame($agent, $agent->setIsDemo($isDemo));
        $this->assertTrue($agent->getIsDemo());
    }
}
