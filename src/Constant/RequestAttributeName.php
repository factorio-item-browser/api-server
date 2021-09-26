<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Constant;

use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Server\Tracking\Event\RequestEvent;
use Mezzio\Router\RouteResult;

/**
 * The interface holding the attribute names used in the requests.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface RequestAttributeName
{
    /**
     * The attribute holding the name of the agent used for the request.
     */
    public const AGENT_NAME = 'agentName';

    /**
     * The attribute holding the combination used for the request.
     */
    public const COMBINATION = Combination::class;

    /**
     * The attribute holding the result of the router.
     */
    public const ROUTE_RESULT = RouteResult::class;

    /**
     * The attribute holding the tracking event for general request-related data.
     */
    public const TRACKING_REQUEST_EVENT = RequestEvent::class;
}
