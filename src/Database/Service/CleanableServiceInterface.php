<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

/**
 * The interface of services which needs cleanup from now and then.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface CleanableServiceInterface
{
    /**
     * Cleans up the service.
     */
    public function cleanup(): void;
}
