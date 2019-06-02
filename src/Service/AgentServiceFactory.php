<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\Entity\Agent;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the agent service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AgentServiceFactory implements FactoryInterface
{
    /**
     * Creates the service.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return AgentService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $projectConfig = $config[ConfigKey::PROJECT][ConfigKey::API_SERVER];

        $agents = [];
        foreach ($projectConfig[ConfigKey::AGENTS] ?? [] as $agentConfig) {
            $agents[] = $this->createAgent($agentConfig);
        }

        return new AgentService($agents);
    }

    /**
     * Creates an agent from the specified config.
     * @param array $agentConfig
     * @return Agent
     */
    protected function createAgent(array $agentConfig): Agent
    {
        $result = new Agent();
        $result->setName($agentConfig[ConfigKey::AGENT_NAME] ?? '')
               ->setAccessKey($agentConfig[ConfigKey::AGENT_ACCESS_KEY] ?? '')
               ->setIsDemo(($agentConfig[ConfigKey::AGENT_DEMO] ?? false) === true);
        return $result;
    }
}
