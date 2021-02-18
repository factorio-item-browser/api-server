<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Client\Request\AbstractRequest;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\Exception\InvalidApiKeyException;
use FactorioItemBrowser\Common\Constant\Defaults;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The middleware to check the authorization to the API.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /** @var array<array{name: string, api-key: string}> */
    private array $agents;

    /**
     * @param array<array{name: string, api-key: string}> $agents
     */
    public function __construct(array $agents)
    {
        $this->agents = $agents;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidApiKeyException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $agentName = $this->getAgentName($request->getHeaderLine('Api-Key'));
        /** @var AbstractRequest $clientRequest */
        $clientRequest = $request->getParsedBody();

        if ($agentName === '' && $clientRequest->combinationId !== Defaults::COMBINATION_ID) {
            throw new InvalidApiKeyException();
        }

        return $handler->handle($request);
    }

    private function getAgentName(string $apiKey): string
    {
        if ($apiKey !== '') {
            foreach ($this->agents as $agent) {
                if ($agent[ConfigKey::AGENT_API_KEY] === $apiKey) {
                    return $agent[ConfigKey::AGENT_NAME];
                }
            }
        }
        return '';
    }
}
