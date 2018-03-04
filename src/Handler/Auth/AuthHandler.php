<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use Firebase\JWT\JWT;
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
     * The key used for creating the authorization token.
     * @var string
     */
    protected $authorizationKey;

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
     * @param string $authorizationKey
     * @param array $agents
     * @param ModService $modService
     */
    public function __construct(string $authorizationKey, array $agents, ModService $modService)
    {
        $this->authorizationKey = $authorizationKey;
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
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $agent = $requestData->getString('agent');
        $accessKey = $requestData->getString('accessKey');
        if (!isset($this->agents[$agent]) || $this->agents[$agent] !== $accessKey) {
            throw new ApiServerException('Invalid agent or access key.', 403);
        }

        $this->modService->setEnabledCombinationsByModNames($requestData->getArray('enabledModNames'));
        $token = [
            'iat' => time(),
            'exp' => time() + 86400,
            'agt' => $requestData->getString('agent'),
            'mds' => $this->modService->getEnabledModCombinationIds()
        ];

        return [
            'authorizationToken' => JWT::encode($token, $this->authorizationKey)
        ];
    }
}