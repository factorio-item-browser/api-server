<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

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

        $agents = [];
        foreach ($config['factorio-item-browser']['api-server']['agents'] ?? [] as $name => $agentConfig) {
            $agents[] = $this->createAgent($name, $agentConfig);
        }

        return new AgentService($agents);
    }

    /**
     * Creates an agent from the specified config.
     * @param string $name
     * @param array $agentConfig
     * @return Agent
     */
    protected function createAgent(string $name, array $agentConfig): Agent
    {
        $result = new Agent();
        $result->setName($name)
               ->setAccessKey($agentConfig['access-key'] ?? '')
               ->setIsDemo(($agentConfig['demo'] ?? false) === true);

        return $result;
    }
}
