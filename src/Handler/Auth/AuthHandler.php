<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Auth;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use Firebase\JWT\JWT;
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
     * The lifetime of a token.
     */
    const TOKEN_LIFETIME = 86400;

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
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $agent = $requestData->getString('agent');
        $accessKey = $requestData->getString('accessKey');
        $agentConfig = $this->agents[$agent] ?? [];
        if (empty($agentConfig) || !isset($agentConfig['accessKey']) || $agentConfig['accessKey'] !== $accessKey) {
            throw new ApiServerException('Invalid agent or access key.', 403);
        }

        $enabledModNames = ($agentConfig['isDemo'] ?? false) ? ['base'] : $requestData->getArray('enabledModNames');
        $this->modService->setEnabledCombinationsByModNames($enabledModNames);

        return [
            'authorizationToken' => $this->createToken(
                $agent,
                $this->modService->getEnabledModCombinationIds(),
                $agentConfig['allowImport'] ?? false
            )
        ];
    }

    /**
     * Creates and returns a new token.
     * @param string $agent
     * @param array $enabledModCombinationIds
     * @param bool $allowImport
     * @return string
     */
    protected function createToken(string $agent, array $enabledModCombinationIds, bool $allowImport): string
    {
        $token = [
            'iat' => time(),
            'exp' => time() + self::TOKEN_LIFETIME,
            'agt' => $agent,
            'mds' => $enabledModCombinationIds
        ];
        if ($allowImport ?? false) {
            $token['imp'] = 1;
        }

        return JWT::encode($token, $this->authorizationKey);
    }
}
