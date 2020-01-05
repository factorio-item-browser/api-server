<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Response;

use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\InjectContentTypeTrait;
use Zend\Diactoros\Stream;

/**
 * The wrapper for the client response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ClientResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * The actual response entity.
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Initializes the response.
     * @param ResponseInterface $response
     * @param int $status
     * @param array|string[] $headers
     */
    public function __construct(ResponseInterface $response, $status = 200, array $headers = [])
    {
        parent::__construct('php://memory', $status, $this->injectContentType('application/json', $headers));

        $this->response = $response;
    }

    /**
     * Returns the actual response entity.
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returns a client response with the serialized response as body.
     * @param string $serializedResponse
     * @return ClientResponse
     */
    public function withSerializedResponse(string $serializedResponse): self
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($serializedResponse);
        $stream->rewind();
        return $this->withBody($stream);
    }
}
