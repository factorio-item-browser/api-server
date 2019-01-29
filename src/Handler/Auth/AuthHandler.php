<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Exception\UnknownAgentException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\AgentService;
use FactorioItemBrowser\Api\Server\Service\AuthorizationService;
use Zend\InputFilter\ArrayInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

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
     * Creates the input filter to use for the request.
     * @return InputFilter
     */
    protected function createInputFilter(): InputFilter
    {
        $inputFilter = new InputFilter();
        $inputFilter
            ->add([
                'name' => 'agent',
                'required' => true,
                'validators' => [
                    new NotEmpty(),
                ],
            ])
            ->add([
                'name' => 'accessKey',
                'required' => true,
                'validators' => [
                    new NotEmpty(),
                ],
            ])
            ->add([
                'type' => ArrayInput::class,
                'name' => 'enabledModNames',
                'required' => true,
                'validators' => [
                    new NotEmpty(),
                ],
            ]);

        return $inputFilter;
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     * @throws ApiServerException
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $agent = $this->getAgentFromRequestData($requestData);
        $enabledModCombinationIds = $this->getEnabledModCombinationIdsFromRequestData($agent, $requestData);
        $token = $this->authorizationService->createToken($agent, $enabledModCombinationIds);

        return [
            'authorizationToken' => $this->authorizationService->serializeToken($token),
        ];
    }

    /**
     * Returns the agent from the request data.
     * @param DataContainer $requestData
     * @return Agent
     * @throws ApiServerException
     */
    protected function getAgentFromRequestData(DataContainer $requestData): Agent
    {
        $result = $this->agentService->getByAccessKey(
            $requestData->getString('agent'),
            $requestData->getString('accessKey')
        );
        if ($result === null) {
            throw new UnknownAgentException();
        }

        return $result;
    }

    /**
     * Returns the enabled mod combination ids from the specified request.
     * @param Agent $agent
     * @param DataContainer $requestData
     * @return array|int[]
     */
    protected function getEnabledModCombinationIdsFromRequestData(Agent $agent, DataContainer $requestData): array
    {
        $enabledModNames = $agent->getIsDemo() ? ['base'] : $requestData->getArray('enabledModNames');
        $this->modService->setEnabledCombinationsByModNames($enabledModNames);
        return $this->modService->getEnabledModCombinationIds();
    }
}
