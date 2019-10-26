<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Response;

use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ClientResponse class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Response\ClientResponse
 */
class ClientResponseTest extends TestCase
{
    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::getResponse
     */
    public function testConstruct(): void
    {
        /* @var ResponseInterface&MockObject $clientResponse */
        $clientResponse = $this->createMock(ResponseInterface::class);
        $statusCode = 123;
        $headers = [
            'abc' => 'def',
        ];

        $response = new ClientResponse($clientResponse, $statusCode, $headers);

        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame('def', $response->getHeaderLine('abc'));
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame($clientResponse, $response->getResponse());
    }

    /**
     * Tests the withSerializedResponse method.
     * @covers ::withSerializedResponse
     */
    public function testWithSerializedResponse(): void
    {
        /* @var ResponseInterface&MockObject $clientResponse */
        $clientResponse = $this->createMock(ResponseInterface::class);
        $statusCode = 123;
        $headers = [
            'abc' => 'def',
        ];
        $serializedResponse = 'ghi';

        $response = new ClientResponse($clientResponse, $statusCode, $headers);
        $result = $response->withSerializedResponse($serializedResponse);

        $this->assertNotSame($response, $result);
        $this->assertSame($statusCode, $result->getStatusCode());
        $this->assertSame('def', $result->getHeaderLine('abc'));
        $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
        $this->assertSame($clientResponse, $result->getResponse());
        $this->assertSame($serializedResponse, $result->getBody()->getContents());
    }
}
