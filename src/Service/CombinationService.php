<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Exception\InternalServerException;
use FactorioItemBrowser\Common\Constant\Constant;
use Ramsey\Uuid\Uuid;

/**
 * The service helping with the combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationService
{
    protected CombinationRepository $combinationRepository;

    protected ?DatabaseMod $baseMod = null;

    public function __construct(CombinationRepository $combinationRepository)
    {
        $this->combinationRepository = $combinationRepository;
    }

    /**
     * Returns the base mod from the default combination.
     * @return DatabaseMod
     * @throws InternalServerException
     */
    public function getBaseMod(): DatabaseMod
    {
        if ($this->baseMod !== null) {
            return $this->baseMod;
        }

        $combination = $this->combinationRepository->findById(Uuid::fromString(Config::DEFAULT_COMBINATION_ID));
        if ($combination !== null) {
            foreach ($combination->getMods() as $mod) {
                if ($mod->getName() === Constant::MOD_NAME_BASE) {
                    $this->baseMod = $mod;
                    return $mod;
                }
            }
        }

        throw new InternalServerException('Missing base mod or default combination');
    }
}
