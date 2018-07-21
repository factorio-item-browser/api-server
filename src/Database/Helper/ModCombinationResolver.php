<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Helper;

use FactorioItemBrowser\Api\Server\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;

/**
 * The class for resolving mod names to their respective combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModCombinationResolver
{
    /**
     * The database mod service.
     * @var ModService
     */
    protected $modService;

    /**
     * Initializes the mod dependency resolver.
     * @param ModService $modService
     */
    public function __construct(ModService $modService)
    {
        $this->modService = $modService;
    }

    /**
     * Resolves the specified mod names to their mod combinations.
     * @param array|string[] $modNames
     * @return array|int[]
     */
    public function resolve(array $modNames): array
    {
        $result = [];
        if (count($modNames) > 0) {
            $combinations = $this->modService->getModCombinationsByModNames($modNames);
            $mods = $this->extractMods($combinations);
            $cleanedCombinations = $this->removeInvalidCombinations($combinations, $mods);
            $result = array_keys($cleanedCombinations);
        }
        return $result;
    }

    /**
     * Extracts the actual mods from the fetched combinations.
     * @param array|ModCombination[] $combinations
     * @return array|Mod[]
     */
    protected function extractMods(array $combinations): array
    {
        $result = [];
        foreach ($combinations as $combination) {
            $result[$combination->getMod()->getId()] = $combination->getMod();
        }
        return $result;
    }

    /**
     * Removes any invalid combinations, which mods are not all enabled.
     * @param array|ModCombination[] $combinations
     * @param array|Mod[] $mods
     * @return array|ModCombination[]
     */
    protected function removeInvalidCombinations(array $combinations, array $mods): array
    {
        foreach ($combinations as $key => $combination) {
            foreach ($combination->getOptionalModIds() as $modId) {
                if (!isset($mods[$modId])) {
                    unset($combinations[$key]);
                    break;
                }
            }
        }
        return $combinations;
    }
}
