<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use FactorioItemBrowser\Api\Client\Request\Auth\AuthRequest;
use FactorioItemBrowser\Api\Client\Response\Auth\AuthResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\UnknownAgentException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\ModResolver\ModCombinationResolver;
use FactorioItemBrowser\Api\Server\ModResolver\ModDependencyResolver;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;

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
     * The mod combination resolver.
     * @var ModCombinationResolver
     */
    protected $modCombinationResolver;

    /**
     * The mod dependency resolver.
     * @var ModDependencyResolver
     */
    protected $modDependencyResolver;

    /**
     * Initializes the auth handler.
     * @param AgentService $agentService
     * @param AuthorizationService $authorizationService
     * @param ModCombinationResolver $modCombinationResolver
     * @param ModDependencyResolver $modDependencyResolver
     */
    public function __construct(
        AgentService $agentService,
        AuthorizationService $authorizationService,
        ModCombinationResolver $modCombinationResolver,
        ModDependencyResolver $modDependencyResolver
    ) {
        $this->authorizationService = $authorizationService;
        $this->agentService = $agentService;
        $this->modCombinationResolver = $modCombinationResolver;
        $this->modDependencyResolver = $modDependencyResolver;
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
        $agent = $this->getAgentFromRequest($request);
        $enabledModCombinationIds = $this->getEnabledModCombinationIdsFromRequest($agent, $request);
        $authorizationToken = $this->createAuthorizationToken($agent, $enabledModCombinationIds);
        return $this->createResponse($authorizationToken);
    }

    /**
     * Returns the agent from the request.
     * @param AuthRequest $clientRequest
     * @return Agent
     * @throws UnknownAgentException
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
     * @return array|int[]
     */
    protected function getEnabledModCombinationIdsFromRequest(Agent $agent, AuthRequest $request): array
    {
        if ($agent->getIsDemo()) {
            $modNames = ['base'];
        } else {
            $modNames = $this->modDependencyResolver->resolve($request->getEnabledModNames());
        }
        return $this->modCombinationResolver->resolve($modNames);
    }

    /**
     * Creates the authorization token for the agent and enabled combinations.
     * @param Agent $agent
     * @param array|int[] $enabledModCombinationIds
     * @return string
     */
    protected function createAuthorizationToken(Agent $agent, array $enabledModCombinationIds): string
    {
        $token = $this->authorizationService->createToken($agent, $enabledModCombinationIds);
        return $this->authorizationService->serializeToken($token);
    }

    /**
     * Creates the response to return.
     * @param string $authorizationToken
     * @return AuthResponse
     */
    protected function createResponse(string $authorizationToken): AuthResponse
    {
        $result = new AuthResponse();
        $result->setAuthorizationToken($authorizationToken);
        return $result;
    }
}
