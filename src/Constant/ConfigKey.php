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
     * The main key of the config.
     */
    public const MAIN = 'api-server';

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
    public const AGENT_API_KEY = 'api-key';

    /**
     * The origins allowed to access the API server.
     */
    public const ALLOWED_ORIGINS = 'allowed-origins';

    /**
     * The key holding the map of the routes to their corresponding requests.
     */
    public const REQUEST_CLASSES_BY_ROUTES = 'request-classes-by-routes';

    /**
     * The key holding the search decorators to use.
     */
    public const SEARCH_DECORATORS = 'search-decorators';

    public const TRACKING = 'tracking';
    public const TRACKING_MEASUREMENT_ID = 'measurement-id';
    public const TRACKING_API_SECRET = 'api-secret';
}
