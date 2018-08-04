<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Database\Repository\ModCombinationRepository;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
use FactorioItemBrowser\Api\Server\Database\Helper\ModCombinationResolver;
use FactorioItemBrowser\Api\Server\Database\Helper\ModDependencyResolver;

/**
 * The service class for the Mod database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModService extends AbstractDatabaseService
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
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->modRepository = $entityManager->getRepository(Mod::class);
        $this->modCombinationRepository = $entityManager->getRepository(ModCombination::class);
        return $this;
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
     * Sets the enabled mods by their names.
     * @param array|string[] $modNames
     * @return $this
     */
    public function setEnabledCombinationsByModNames(array $modNames)
    {
        $dependencyResolver = new ModDependencyResolver($this);
        $allModNames = $dependencyResolver->resolve($modNames);

        $combinationResolver = new ModCombinationResolver($this);
        $this->setEnabledModCombinationIds($combinationResolver->resolve($allModNames));
        return $this;
    }

    /**
     * Calculates the hash representing the currently enabled combination ids.
     * @return string
     */
    public function calculateCombinationHash()
    {
        return hash('crc32b', implode(',', $this->enabledModCombinationIds));
    }

    /**
     * Returns the mods with the specified names with their dependencies already fetched.
     * @param array|string[] $modNames
     * @return array|Mod[] The found mods. Keys are the mod names.
     */
    public function getModsWithDependencies(array $modNames): array
    {
        $result = [];
        foreach ($this->modRepository->findByNamesWithDependencies($modNames) as $mod) {
            $result[$mod->getName()] = $mod;
        }
        return $result;
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
            /* @var Mod $mod */
            $result[$mod->getName()] = $mod;
        }
        return $result;
    }

    /**
     * Returns all known combinations.
     * @return array|ModCombination[]
     */
    public function getAllCombinations(): array
    {
        $result = [];
        foreach ($this->modCombinationRepository->findAll() as $combination) {
            /* @var ModCombination $combination */
            $result[$combination->getName()] = $combination;
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
