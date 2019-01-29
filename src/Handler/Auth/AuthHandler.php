<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
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
     * The authorization service.
     * @var AuthorizationService
     */
    protected $authorizationService;

    /**
     * The agents of the API.
     * @var array
     */
    protected $agents;

    /**
     * The database mod service.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the auth handler.
     * @param AuthorizationService $authorizationService
     * @param array $agents
     * @param ModService $modService
     */
    public function __construct(AuthorizationService $authorizationService, array $agents, ModService $modService)
    {
        $this->authorizationService = $authorizationService;
        $this->agents = $agents;
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
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'accessKey',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'type' => ArrayInput::class,
                'name' => 'enabledModNames',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
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
        $agent = $requestData->getString('agent');
        $accessKey = $requestData->getString('accessKey');
        $agentConfig = $this->agents[$agent] ?? [];
        if (!is_array($agentConfig) || !isset($agentConfig['accessKey']) || $agentConfig['accessKey'] !== $accessKey) {
            throw new ApiServerException('Invalid agent or access key.', 403);
        }

        $enabledModNames = ($agentConfig['isDemo'] ?? false) ? ['base'] : $requestData->getArray('enabledModNames');
        $this->modService->setEnabledCombinationsByModNames($enabledModNames);

        $token = new AuthorizationToken();
        $token->setAgent($agent)
              ->setEnabledModCombinationIds($this->modService->getEnabledModCombinationIds());

        return [
            'authorizationToken' => $this->authorizationService->serializeToken($token)
        ];
    }
}
