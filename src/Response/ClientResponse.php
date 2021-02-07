<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Response;

use JMS\Serializer\SerializerInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Laminas\Diactoros\Stream;

/**
 * The response wrapping around the client response object.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ClientResponse extends Response
{
    use InjectContentTypeTrait;

    private object $payload;

    /**
     * @param object $payload
     * @param int $statusCode
     * @param array<string, string> $headers
     */
    public function __construct(object $payload, int $statusCode = 200, array $headers = [])
    {
        parent::__construct('php://memory', $statusCode, $this->injectContentType('application/json', $headers));
        $this->payload = $payload;
    }

    public function getPayload(): object
    {
        return $this->payload;
    }

    public function withSerializer(SerializerInterface $serializer): self
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($serializer->serialize($this->payload, 'json'));
        $stream->rewind();
        return $this->withBody($stream);
    }
}
