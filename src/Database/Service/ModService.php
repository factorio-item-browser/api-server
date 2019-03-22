<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

/**
 * The service class for the Mod database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @deprecated
 */
class ModService
{
    /**
     * The ids of the enabled mod combinations.
     * @var array|int[]
     */
    protected $enabledModCombinationIds = [];

    /**
     * Sets the ids of the enabled mod combinations.
     * @param array|int[] $modCombinationIds
     * @return $this
     */
    public function setEnabledModCombinationIds(array $modCombinationIds)
    {
        $this->enabledModCombinationIds = $modCombinationIds;
        return $this;
    }

    /**
     * Returns the the ids of the enabled mod combinations.
     * @return array|int[]
     */
    public function getEnabledModCombinationIds(): array
    {
        return $this->enabledModCombinationIds;
    }

}
