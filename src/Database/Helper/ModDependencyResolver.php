<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Helper;

use FactorioItemBrowser\Api\Server\Database\Constant\ModDependencyType;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;

/**
 * The class able to resolve any dependencies of given mod names.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModDependencyResolver
{
    /**
     * The database mod service.
     * @var ModService
     */
    protected $modService;

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
     * @param ModService $modService
     */
    public function __construct(ModService $modService)
    {
        $this->modService = $modService;
    }

    /**
     * Resolves the specified mod names, adding any required mod to the list.
     * @param array|string[] $modNames
     * @return array|string[]
     */
    public function resolve(array $modNames): array
    {
        $this->resolvedMods = [];
        if (count($modNames) > 0) {
            $this->fetchMods($modNames);

            foreach ($modNames as $modName) {
                $this->processMod($modName);
            }
        }
        return array_keys($this->resolvedMods);
    }

    /**
     * Fetches the specified mods if they are still missing.
     * @param array|string[] $modNames
     * @return $this
     */
    protected function fetchMods(array $modNames)
    {
        $missingModNames = array_diff($modNames, array_keys($this->fetchedMods));
        $this->fetchedMods = array_merge(
            $this->fetchedMods,
            $this->modService->getModsWithDependencies($missingModNames)
        );
        return $this;
    }

    /**
     * Processes the mod with the specified name.
     * @param string $modName
     * @return $this
     */
    protected function processMod(string $modName)
    {
        if (!isset($this->resolvedMods[$modName])) {
            $this->fetchMods([$modName]);
            if (isset($this->fetchedMods[$modName])) {
                foreach ($this->fetchedMods[$modName]->getDependencies() as $dependency) {
                    if ($dependency->getType() === ModDependencyType::MANDATORY
                        && !isset($this->resolvedMods[$dependency->getRequiredMod()->getName()])
                    ) {
                        $this->processMod($dependency->getRequiredMod()->getName());
                    }
                }
                $this->resolvedMods[$modName] = true;
            }
        }
        return $this;
    }
}