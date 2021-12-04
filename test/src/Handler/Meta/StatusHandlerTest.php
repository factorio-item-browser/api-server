<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Meta;

use DateTime;
use FactorioItemBrowser\Api\Client\Response\Meta\StatusResponse;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Server\Handler\Meta\StatusHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PHPUnit test of the StatusHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \FactorioItemBrowser\Api\Server\Handler\Meta\StatusHandler
 */
class StatusHandlerTest extends TestCase
{
    /**
     * @param array<string> $mockedMethods
     * @return StatusHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): StatusHandler
    {
        return $this->getMockBuilder(StatusHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->getMock();
    }

    public function testHandle(): void
    {
        $importTime = new DateTime('2038-01-19T03:14:07+00:00');

        $combination = new Combination();
        $combination->setImportTime($importTime);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getAttribute')
                ->with($this->identicalTo(Combination::class))
                ->willReturn($combination);

        $expectedPayload = new StatusResponse();
        $expectedPayload->dataVersion = 1;
        $expectedPayload->importTime = $importTime;

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }
}
