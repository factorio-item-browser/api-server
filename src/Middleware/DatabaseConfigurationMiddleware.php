<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * The middleware actually configuring the database connection.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DatabaseConfigurationMiddleware implements MiddlewareInterface
{
    /**
     * The default config key to use.
     */
    private const DEFAULT_CONFIG_KEY = 'default';

    /**
     * The map of routes to the config keys to be used on those.
     */
    private const CONFIG_KEYS_BY_ROUTE = [
        '/import' => 'import'
    ];

    /**
     * The service manager.
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * The configuration aliases.
     * @var array
     */
    protected $configurationAliases;

    /**
     * Initializes the middleware.
     * @param ServiceManager $serviceManager
     * @param array $configurationAliases
     */
    public function __construct(ServiceManager $serviceManager, array $configurationAliases)
    {
        $this->serviceManager = $serviceManager;
        $this->configurationAliases = $configurationAliases;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getRequestTarget();
        $configKey = self::CONFIG_KEYS_BY_ROUTE[$route] ?? self::DEFAULT_CONFIG_KEY;
        $this->injectDatabaseConfiguration($configKey);

        return $handler->handle($request);
    }

    /**
     * Injects the configuration of the specified key.
     * @param string $configKey
     * @return $this
     */
    protected function injectDatabaseConfiguration(string $configKey)
    {
        $config = $this->serviceManager->get('config');
        $connectionConfig = $config['doctrine']['connection'];

        $connectionConfig['orm_default'] = $connectionConfig[$this->configurationAliases[$configKey] ?? ''] ?? [];
        $config['doctrine']['connection'] = $connectionConfig;

        $allowOverride = $this->serviceManager->getAllowOverride();
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('config', $config);
        $this->serviceManager->setAllowOverride($allowOverride);

        return $this;
    }
}