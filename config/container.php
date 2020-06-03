<?php

declare(strict_types=1);

/**
 * The file providing the container.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

// Load configuration
$config = require(__DIR__ . '/config.php');

// Build container
$container = new ServiceManager();
(new Config($config['dependencies']))->configureServiceManager($container);

// Inject config
$container->setService('config', $config);
$container->setService(OutputInterface::class, new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, true));

if ($config[ConfigAggregator::ENABLE_CACHE] ?? false) {
    AutoWireFactory::setCacheFile(__DIR__ . '/../data/cache/autowire-factory-cache.php');
}

return $container;
