<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Ga4MeasurementProtocol\ClientInterface;
use BluePsyduck\Ga4MeasurementProtocol\Request\Payload;
use FactorioItemBrowser\Api\Server\Service\TrackingService;
use FactorioItemBrowser\Api\Server\Tracking\Event\RequestEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * The PHPUnit test of the TrackingService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Service\TrackingService
 */
class TrackingServiceTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return TrackingService&MockObject
     */
    private function createInstance(array $mockedMethods = []): TrackingService
    {
        return $this->getMockBuilder(TrackingService::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->client,
                    ])
                    ->getMock();
    }

    public function testTrack(): void
    {
        $event1 = new RequestEvent();
        $event1->agentName = 'abc';
        $event2 = new RequestEvent();
        $event2->agentName = 'def';

        $expectedPayload = new Payload();
        $expectedPayload->events = [$event1, $event2];

        $this->client->expects($this->once())
                     ->method('send')
                     ->with($this->callback(function (Payload $payload) use ($expectedPayload): bool {
                         $this->assertIsString($payload->clientId);
                         $payload->clientId = null;
                         $this->assertEquals($expectedPayload, $payload);
                         return true;
                     }));

        $instance = $this->createInstance();
        $instance->addEvent($event1);
        $instance->addEvent($event2);
        $instance->track();
    }

    public function testTrackWithException(): void
    {
        $event1 = new RequestEvent();
        $event1->agentName = 'abc';
        $event2 = new RequestEvent();
        $event2->agentName = 'def';

        $expectedPayload = new Payload();
        $expectedPayload->events = [$event1, $event2];

        $this->client->expects($this->once())
                     ->method('send')
                     ->with($this->callback(function (Payload $payload) use ($expectedPayload): bool {
                         $this->assertIsString($payload->clientId);
                         $payload->clientId = null;
                         $this->assertEquals($expectedPayload, $payload);
                         return true;
                     }))
                     ->willThrowException($this->createMock(ClientExceptionInterface::class));

        $instance = $this->createInstance();
        $instance->addEvent($event1);
        $instance->addEvent($event2);
        $instance->track();
    }
}
