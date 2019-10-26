<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use FactorioItemBrowser\Api\Client\Request\Auth\AuthRequest;
use FactorioItemBrowser\Api\Client\Response\Auth\AuthResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\UnknownAgentException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use Ramsey\Uuid\Uuid;

/**
 * The handler of the /auth request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AuthHandler extends AbstractRequestHandler
{
    /**
     * The agent service.
     * @var AgentService
     */
    protected $agentService;

    /**
     * The authorization service.
     * @var AuthorizationService
     */
    protected $authorizationService;

    /**
     * Initializes the auth handler.
     * @param AgentService $agentService
     * @param AuthorizationService $authorizationService
     */
    public function __construct(
        AgentService $agentService,
        AuthorizationService $authorizationService
    ) {
        $this->authorizationService = $authorizationService;
        $this->agentService = $agentService;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return AuthRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param AuthRequest $request
     * @return ResponseInterface
     * @throws ApiServerException
     */
    protected function handleRequest($request): ResponseInterface
    {
        $token = $this->createAuthorizationToken($request);

        return $this->createResponse($token);
    }

    /**
     * Creates the authorization token for the request.
     * @param AuthRequest $request
     * @return AuthorizationToken
     * @throws ApiServerException
     */
    protected function createAuthorizationToken(AuthRequest $request): AuthorizationToken
    {
        $agent = $this->getAgentFromRequest($request);
        $modNames = $this->getModModNamesFromRequest($agent, $request);

        $token = new AuthorizationToken();
        $token->setAgentName($agent->getName())
              ->setCombinationId(Uuid::fromString('6E6F47E8572744C0B759EF9ED5F994A2'))
              ->setModNames($modNames);

        return $token;
    }

    /**
     * Returns the agent from the request.
     * @param AuthRequest $clientRequest
     * @return Agent
     * @throws ApiServerException
     */
    protected function getAgentFromRequest(AuthRequest $clientRequest): Agent
    {
        $result = $this->agentService->getByAccessKey(
            $clientRequest->getAgent(),
            $clientRequest->getAccessKey()
        );
        if ($result === null) {
            throw new UnknownAgentException();
        }

        return $result;
    }

    /**
     * Returns the enabled mod combination ids from the specified request.
     * @param Agent $agent
     * @param AuthRequest $request
     * @return array|string[]
     */
    protected function getModModNamesFromRequest(Agent $agent, AuthRequest $request): array
    {
        if ($agent->getIsDemo()) {
            return ['base'];
        }
        return $request->getEnabledModNames();
    }

    /**
     * Creates the response to return.
     * @param AuthorizationToken $authorizationToken
     * @return AuthResponse
     */
    protected function createResponse(AuthorizationToken $authorizationToken): AuthResponse
    {
        $result = new AuthResponse();
        $result->setAuthorizationToken($this->authorizationService->serializeToken($authorizationToken));
        return $result;
    }
}
