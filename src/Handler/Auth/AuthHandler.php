<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use FactorioItemBrowser\Api\Client\Request\Auth\AuthRequest;
use FactorioItemBrowser\Api\Client\Response\Auth\AuthResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\UnknownAgentException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
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
     * The database mod service.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the auth handler.
     * @param AgentService $agentService
     * @param AuthorizationService $authorizationService
     * @param ModService $modService
     */
    public function __construct(
        AgentService $agentService,
        AuthorizationService $authorizationService,
        ModService $modService
    ) {
        $this->authorizationService = $authorizationService;
        $this->agentService = $agentService;
        $this->modService = $modService;
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
        $agent = $this->getAgentFromRequestData($request);
        $enabledModCombinationIds = $this->getEnabledModCombinationIdsFromRequest($agent, $request);
        $authorizationToken = $this->createAuthorizationToken($agent, $enabledModCombinationIds);

        $response = new AuthResponse();
        $response->setAuthorizationToken($authorizationToken);
        return $response;
    }

    /**
     * Returns the agent from the request data.
     * @param AuthRequest $clientRequest
     * @return Agent
     * @throws UnknownAgentException
     */
    protected function getAgentFromRequestData(AuthRequest $clientRequest): Agent
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
        $enabledModNames = $agent->getIsDemo() ? ['base'] : $request->getEnabledModNames();
        $this->modService->setEnabledCombinationsByModNames($enabledModNames);
        return $this->modService->getEnabledModCombinationIds();
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
}
