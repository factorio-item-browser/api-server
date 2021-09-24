<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Tracking\Event;

use FactorioItemBrowser\Api\Server\Tracking\Event\RequestEvent;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the RequestEvent class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Tracking\Event\RequestEvent
 */
class RequestEventTest extends TestCase
{
    public function testWithData(): void
    {
        $event = new RequestEvent();
        $event->agentName = 'abc';
        $event->routeName = 'def';
        $event->locale = 'ghi';
        $event->runtime = 13.37;
        $event->statusCode = 200;
        $event->combinationId = 'jkl';
        $event->modCount = 42;

        $expectedParams = [
            'agent_name' => 'abc',
            'route_name' => 'def',
            'locale' => 'ghi',
            'runtime' => 13.37,
            'status_code' => 200,
            'combination_id' => 'jkl',
            'mod_count' => 42,
        ];

        $this->assertSame('request', $event->getName());
        $this->assertSame($expectedParams, $event->getParams());
    }

    public function testWithoutData(): void
    {
        $event = new RequestEvent();

        $this->assertSame('request', $event->getName());
        $this->assertSame([], $event->getParams());
    }
}
