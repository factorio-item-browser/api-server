<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use FactorioItemBrowser\Api\Client\Request\Auth\AuthRequest;
use FactorioItemBrowser\Api\Client\Response\Auth\AuthResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\MissingBaseModException;
use FactorioItemBrowser\Api\Server\Exception\InvalidAccessKeyException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use FactorioItemBrowser\Common\Constant\Constant;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
        $modNames = $this->getModNamesFromRequest($agent, $request);

        $token = new AuthorizationToken();
        $token->setAgentName($agent->getName())
              ->setCombinationId($this->calculateCombinationId($modNames))
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
        $result = $this->agentService->getByAccessKey($clientRequest->getAccessKey());
        if ($result === null) {
            throw new InvalidAccessKeyException();
        }

        return $result;
    }

    /**
     * Returns the enabled mod combination ids from the specified request.
     * @param Agent $agent
     * @param AuthRequest $request
     * @return array|string[]
     * @throws ApiServerException
     */
    protected function getModNamesFromRequest(Agent $agent, AuthRequest $request): array
    {
        if ($agent->getIsDemo()) {
            return [Constant::MOD_NAME_BASE];
        }
        $modNames = $request->getModNames();
        if (!in_array(Constant::MOD_NAME_BASE, $modNames, true)) {
            throw new MissingBaseModException();
        }
        return $modNames;
    }

    /**
     * Calculates and returns the combination id to use for the mod names.
     * @param array|string[] $modNames
     * @return UuidInterface
     */
    protected function calculateCombinationId(array $modNames): UuidInterface
    {
        $modNames = array_map(function (string $modName): string {
            return trim($modName);
        }, $modNames);
        sort($modNames);

        $hash = hash('md5', (string) json_encode($modNames));
        return Uuid::fromString($hash);
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
