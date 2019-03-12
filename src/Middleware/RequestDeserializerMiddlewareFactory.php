<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use FactorioItemBrowser\Api\Client\Constant\ServiceName;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use Interop\Container\ContainerInterface;
use JMS\Serializer\SerializerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory of the request deserializer middleware.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RequestDeserializerMiddlewareFactory implements FactoryInterface
{
    /**
     * Creates the meta middleware.
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return RequestDeserializerMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $projectConfig = $config[ConfigKey::PROJECT][ConfigKey::API_SERVER];

        /* @var SerializerInterface $serializer */
        $serializer = $container->get(ServiceName::SERIALIZER);

        return new RequestDeserializerMiddleware($serializer, $projectConfig[ConfigKey::MAP_ROUTE_TO_REQUEST]);
    }
}
