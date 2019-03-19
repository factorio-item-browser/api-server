<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Repository\ModCombinationRepository;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;

/**
 * The service class for the Mod database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModService
{
    /**
     * The repository of the mods.
     * @var ModRepository
     */
    protected $modRepository;

    /**
     * The repository of the mod combinations.
     * @var ModCombinationRepository
     */
    protected $modCombinationRepository;

    /**
     * The ids of the enabled mod combinations.
     * @var array|int[]
     */
    protected $enabledModCombinationIds = [];

    /**
     * ModService constructor.
     * @param ModRepository $modRepository
     * @param ModCombinationRepository $modCombinationRepository
     */
    public function __construct(ModRepository $modRepository, ModCombinationRepository $modCombinationRepository)
    {
        $this->modRepository = $modRepository;
        $this->modCombinationRepository = $modCombinationRepository;
    }

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

    /**
     * Returns the names of the currently enabled mods.
     * @return array|string[]
     */
    public function getEnabledModNames(): array
    {
        return $this->modCombinationRepository->findModNamesByIds($this->enabledModCombinationIds);
    }

    /**
     * Returns all the mod combinations of the specified mod names.
     * @param array $modNames
     * @return array
     */
    public function getModCombinationsByModNames(array $modNames): array
    {
        $result = [];
        foreach ($this->modCombinationRepository->findByModNames($modNames) as $modCombination) {
            $result[$modCombination->getId()] = $modCombination;
        }
        return $result;
    }

    /**
     * Returns all known mods.
     * @return array|Mod[]
     */
    public function getAllMods(): array
    {
        $result = [];
        foreach ($this->modRepository->findAll() as $mod) {
            $result[$mod->getName()] = $mod;
        }
        return $result;
    }

    /**
     * Returns the number of available mods.
     * @return int
     */
    public function getNumberOfAvailableMods(): int
    {
        return $this->modRepository->count();
    }

    /**
     * Returns the number of enabled mods.
     * @return int
     */
    public function getNumberOfEnabledMods(): int
    {
        return $this->modRepository->count($this->enabledModCombinationIds);
    }
}
