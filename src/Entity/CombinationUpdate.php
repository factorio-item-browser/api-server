<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Entity;

use FactorioItemBrowser\Api\Database\Entity\Combination;

/**
 * The entity representing a combination which may need an update.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationUpdate
{
    public Combination $combination;
    public bool $hasBaseModUpdate = false;
    public int $numberOfModUpdates = 0;
    public int $secondsSinceLastImport = 0;
    public int $secondsSinceLastUsage = 0;
    public string $exportStatus = '';
}
