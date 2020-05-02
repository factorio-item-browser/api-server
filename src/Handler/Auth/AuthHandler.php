<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use FactorioItemBrowser\Api\Client\Request\Auth\AuthRequest;
use FactorioItemBrowser\Api\Client\Response\Auth\AuthResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
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
     * The combination repository.
     * @var CombinationRepository
     */
    protected $combinationRepository;

    /**
     * Initializes the auth handler.
     * @param AgentService $agentService
     * @param AuthorizationService $authorizationService
     * @param CombinationRepository $combinationRepository
     */
    public function __construct(
        AgentService $agentService,
        AuthorizationService $authorizationService,
        CombinationRepository $combinationRepository
    ) {
        $this->authorizationService = $authorizationService;
        $this->agentService = $agentService;
        $this->combinationRepository = $combinationRepository;
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
        $combinationId = $this->calculateCombinationId($modNames);

        $token = new AuthorizationToken();
        $token->setAgentName($agent->getName())
              ->setCombinationId($combinationId)
              ->setModNames($modNames);

        $combination = $this->combinationRepository->findById($combinationId);
        if ($combination !== null) {
            $this->combinationRepository->updateLastUsageTime($combination);
        }

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
     */
    protected function getModNamesFromRequest(Agent $agent, AuthRequest $request): array
    {
        if ($agent->getIsDemo()) {
            return [Constant::MOD_NAME_BASE];
        }
        return $request->getModNames();
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
