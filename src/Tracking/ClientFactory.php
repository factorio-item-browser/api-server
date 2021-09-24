<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Tracking;

use BluePsyduck\Ga4MeasurementProtocol\Client;
use BluePsyduck\Ga4MeasurementProtocol\ClientInterface;
use BluePsyduck\Ga4MeasurementProtocol\Config;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * The factory for the tracking client.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ClientFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ClientInterface
    {
        $config = $container->get('config')[ConfigKey::MAIN][ConfigKey::TRACKING];

        $guzzleClient = new GuzzleClient();
        $httpFactory = new HttpFactory();

        $clientConfig = new Config();
        $clientConfig->measurementId = $config[ConfigKey::TRACKING_MEASUREMENT_ID];
        $clientConfig->apiSecret = $config[ConfigKey::TRACKING_API_SECRET];

        return new Client($guzzleClient, $httpFactory, $httpFactory, $clientConfig);
    }
}
