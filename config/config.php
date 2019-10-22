<?php

declare(strict_types=1);

/**
 * The main configuration file.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server;

use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    // Include cache configuration
    new ArrayProvider($cacheConfig),

    \Blast\BaseUrl\ConfigProvider::class,
    \BluePsyduck\MapperManager\ConfigProvider::class,
    \FactorioItemBrowser\Api\Client\ConfigProvider::class,
    \FactorioItemBrowser\Api\Database\ConfigProvider::class,
    \FactorioItemBrowser\Api\Search\ConfigProvider::class,
    \Zend\Expressive\Helper\ConfigProvider::class,
    \Zend\Expressive\ConfigProvider::class,
    \Zend\Expressive\Router\ConfigProvider::class,
    \Zend\Expressive\Router\FastRouteRouter\ConfigProvider::class,
    \Zend\HttpHandlerRunner\ConfigProvider::class,
    \Zend\Log\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `*.global.php`
    //   - `[FIB_ENV]/*.local.php`
    new PhpFileProvider(
        realpath(__DIR__) . sprintf('/autoload/{*.global.php,%s/*.local.php}', getenv('FIB_ENV') ?: 'production')
    ),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
