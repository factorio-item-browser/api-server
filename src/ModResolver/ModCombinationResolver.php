<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\ModResolver;

use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Database\Entity\ModCombination;
use FactorioItemBrowser\Api\Database\Repository\ModCombinationRepository;

/**
 * The class for resolving mod names to their respective combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModCombinationResolver
{
    /**
     * The mod combination repository.
     * @var ModCombinationRepository
     */
    protected $modCombinationRepository;

    /**
     * Initializes the mod dependency resolver.
     * @param ModCombinationRepository $modCombinationRepository
     */
    public function __construct(ModCombinationRepository $modCombinationRepository)
    {
        $this->modCombinationRepository = $modCombinationRepository;
    }

    /**
     * Resolves the specified mod names to their mod combinations.
     * @param array|string[] $modNames
     * @return array|int[]
     */
    public function resolve(array $modNames): array
    {
        $combinations = $this->fetchCombinations($modNames);
        $mods = $this->extractMods($combinations);
        $cleanedCombinations = $this->removeInvalidCombinations($combinations, $mods);
        return array_keys($cleanedCombinations);
    }

    /**
     * Fetches and returns the combinations of the mod names.
     * @param array|string[] $modNames
     * @return array|ModCombination[] Keys are the combination ids, values the actual combinations.
     */
    protected function fetchCombinations(array $modNames): array
    {
        $result = [];
        foreach ($this->modCombinationRepository->findByModNames($modNames) as $combination) {
            $result[$combination->getId()] = $combination;
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
     * Removes any invalid combinations, of which not all optional mods are enabled.
     * @param array|ModCombination[] $combinations
     * @param array|Mod[] $mods The keys must be the mod id, the values the actual mod.
     * @return array|ModCombination[]
     */
    protected function removeInvalidCombinations(array $combinations, array $mods): array
    {
        foreach ($combinations as $key => $combination) {
            if (!$this->isCombinationValid($combination, $mods)) {
                unset($combinations[$key]);
            }
        }
        return $combinations;
    }

    /**
     * Checks whether the combination is valid with the mods.
     * @param ModCombination $combination
     * @param array|Mod[] $mods The keys must be the mod id, the values the actual mod.
     * @return bool
     */
    protected function isCombinationValid(ModCombination $combination, array $mods): bool
    {
        $result = true;
        foreach ($combination->getOptionalModIds() as $optionalModId) {
            if (!isset($mods[$optionalModId])) {
                $result = false;
                break;
            }
        }
        return $result;
    }
}
