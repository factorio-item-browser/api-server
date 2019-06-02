<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Response;

use FactorioItemBrowser\Api\Server\Constant\ServiceName;
use Interop\Container\ContainerInterface;
use Zend\Log\LoggerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the error response generator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ErrorResponseGeneratorFactory implements FactoryInterface
{
    /**
     * Creates the error response generator.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ErrorResponseGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        return new ErrorResponseGenerator($this->fetchLogger($container), $config['debug'] ?? false);
    }

    /**
     * Fetches and returns the logger from the container.
     * @param ContainerInterface $container
     * @return LoggerInterface|null
     */
    protected function fetchLogger(ContainerInterface $container): ?LoggerInterface
    {
        $result = null;
        if ($container->has(ServiceName::LOGGER)) {
            $result = $container->get(ServiceName::LOGGER);
        }
        return $result;
    }
}
