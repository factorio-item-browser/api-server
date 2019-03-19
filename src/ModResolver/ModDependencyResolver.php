<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\ModResolver;

use FactorioItemBrowser\Api\Database\Constant\ModDependencyType;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Entity\ModDependency;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;

/**
 * The class able to resolve any dependencies of given mod names.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDependencyResolver
{
    /**
     * The mod repository.
     * @var ModRepository
     */
    protected $modRepository;

    /**
     * The mods already fetched from the database.
     * @var array|Mod[]
     */
    protected $fetchedMods = [];

    /**
     * The resolved mods.
     * @var array|bool[]
     */
    protected $resolvedMods = [];

    /**
     * Initializes the mod dependency resolver.
     * @param ModRepository $modRepository
     */
    public function __construct(ModRepository $modRepository)
    {
        $this->modRepository = $modRepository;
    }

    /**
     * Resolves the specified mod names, adding any required mod to the list.
     * @param array|string[] $modNames
     * @return array|string[]
     */
    public function resolve(array $modNames): array
    {
        $this->reset();
        $this->fetchMods($modNames);

        foreach ($modNames as $modName) {
            $this->processModWithName($modName);
        }
        return array_keys($this->resolvedMods);
    }

    /**
     * Resets the resolver.
     */
    protected function reset(): void
    {
        $this->resolvedMods = [];
    }

    /**
     * Fetches the specified mods if they are still missing.
     * @param array|string[] $modNames
     */
    protected function fetchMods(array $modNames): void
    {
        $missingModNames = array_values(array_diff($modNames, array_keys($this->fetchedMods)));
        $this->fetchedMods = array_merge(
            $this->fetchedMods,
            $this->fetchModsWithDependencies($missingModNames)
        );
    }

    /**
     * Fetches the mods with their dependencies.
     * @param array|string[] $modNames
     * @return array|Mod[] Keys are the mod names, values the actual mods.
     */
    protected function fetchModsWithDependencies(array $modNames): array
    {
        $result = [];
        foreach ($this->modRepository->findByNamesWithDependencies($modNames) as $mod) {
            $result[$mod->getName()] = $mod;
        }
        return $result;
    }

    /**
     * Processes the mod with the name.
     * @param string $modName
     */
    protected function processModWithName(string $modName): void
    {
        $this->fetchMods([$modName]);

        if (isset($this->fetchedMods[$modName])) {
            foreach ($this->fetchedMods[$modName]->getDependencies() as $dependency) {
                $this->processDependency($dependency);
            }
            $this->resolvedMods[$modName] = true;
        }
    }

    /**
     * Processes the dependency.
     * @param ModDependency $dependency
     */
    protected function processDependency(ModDependency $dependency): void
    {
        $requiredModName = $dependency->getRequiredMod()->getName();
        if ($dependency->getType() === ModDependencyType::MANDATORY
            && !isset($this->resolvedMods[$requiredModName])
        ) {
            $this->processModWithName($requiredModName);
        }
    }
}
