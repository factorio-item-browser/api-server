<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Constant;

/**
 * The interface holding the config keys.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ConfigKey
{
    /**
     * The key holding the name of the project.
     */
    public const PROJECT = 'factorio-item-browser';

    /**
     * The key holding the name of the API server itself.
     */
    public const API_SERVER = 'api-server';

    /**
     * The key holding the agents.
     */
    public const AGENTS = 'agents';

    /**
     * The key holding the name of the agent.
     */
    public const AGENT_NAME = 'name';

    /**
     * The key holding the access key of the agent.
     */
    public const AGENT_ACCESS_KEY = 'access-key';

    /**
     * The key holding the demo flag of the agent.
     */
    public const AGENT_DEMO = 'demo';

    /**
     * The origins allowed to access the API server.
     */
    public const ALLOWED_ORIGINS = 'allowed-origins';

    /**
     * The key holding the authorization config.
     */
    public const AUTHORIZATION = 'authorization';

    /**
     * The key holding the actual authorization key.
     */
    public const AUTHORIZATION_KEY = 'key';

    /**
     * The key holding the lifetime of authorization tokens.
     */
    public const AUTHORIZATION_TOKEN_LIFETIME = 'token-lifetime';

    /**
     * The key holding the map of the routes to their corresponding requests.
     */
    public const MAP_ROUTE_TO_REQUEST = 'map-route-to-request';

    /**
     * The key holding the search decorators to use.
     */
    public const SEARCH_DECORATORS = 'search-decorators';
}
