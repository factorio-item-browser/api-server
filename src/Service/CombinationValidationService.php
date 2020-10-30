<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Constant\DependencyType;
use BluePsyduck\FactorioModPortalClient\Entity\Dependency;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use FactorioItemBrowser\Api\Client\Constant\ValidatedModIssueType;
use FactorioItemBrowser\Api\Client\Entity\ValidatedMod;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Exception\InternalServerException;
use FactorioItemBrowser\Common\Constant\Constant;

/**
 * THe service helping with validating combinations of mods.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationValidationService
{
    protected CombinationService $combinationService;
    protected ModPortalService $modPortalService;

    public function __construct(CombinationService $combinationService, ModPortalService $modPortalService)
    {
        $this->combinationService = $combinationService;
        $this->modPortalService = $modPortalService;
    }

    /**
     * Validates the mods specified by their names, looking for their dependencies and any conflicts.
     * @param array<string> $modNames
     * @return array<string,ValidatedMod>|ValidatedMod[]
     * @throws ClientException
     * @throws InternalServerException
     */
    public function validate(array $modNames): array
    {
        $baseMod = $this->combinationService->getBaseMod();

        $mods = $this->modPortalService->getMods($modNames);
        $releases = $this->modPortalService->getLatestReleases($modNames, $baseMod->getVersion());
        $validatedMods = [];
        foreach ($modNames as $modName) {
            if ($modName === Constant::MOD_NAME_BASE) {
                $validatedMods[$modName] = $this->createValidatedBaseMod($baseMod);
            } elseif (!isset($mods[$modName])) {
                $validatedMods[$modName] = $this->createMissingMod($modName);
            } else {
                $validatedMods[$modName] = $this->validateMod($modName, $releases);
            }
        }
        return $validatedMods;
    }

    protected function createValidatedBaseMod(DatabaseMod $baseMod): ValidatedMod
    {
        $validatedMod = new ValidatedMod();
        $validatedMod->setName($baseMod->getName())
                     ->setVersion($baseMod->getVersion());
        return $validatedMod;
    }

    protected function createMissingMod(string $modName): ValidatedMod
    {
        $validatedMod = new ValidatedMod();
        $validatedMod->setName($modName)
                     ->setIssueType(ValidatedModIssueType::MISSING_MOD);
        return $validatedMod;
    }

    /**
     * @param string $modName
     * @param array<string,Release>|Release[] $releases
     * @return ValidatedMod
     */
    protected function validateMod(string $modName, array $releases): ValidatedMod
    {
        $validatedMod = new ValidatedMod();
        $validatedMod->setName($modName);

        if (!isset($releases[$modName])) {
            $validatedMod->setIssueType(ValidatedModIssueType::MISSING_RELEASE);
            return $validatedMod;
        }

        $release = $releases[$modName];
        $validatedMod->setVersion((string) $release->getVersion());

        foreach ($release->getInfoJson()->getDependencies() as $dependency) {
            $type = $this->validateDependency($dependency, $releases);
            if ($type !== ValidatedModIssueType::NONE) {
                $validatedMod->setIssueType($type)
                             ->setIssueDependency((string) $dependency);
                return $validatedMod;
            }
        }

        return $validatedMod;
    }

    /**
     * @param Dependency $dependency
     * @param array<string,Release>|Release[] $releases
     * @return string
     */
    protected function validateDependency(Dependency $dependency, array $releases): string
    {
        if ($dependency->getMod() === Constant::MOD_NAME_BASE) {
            // The base mod is validated through the factorio_version of the releases.
            return ValidatedModIssueType::NONE;
        }

        $dependentRelease = $releases[$dependency->getMod()] ?? null;

        if (
            $dependency->getType() === DependencyType::MANDATORY
            && ($dependentRelease === null || !$dependency->isMatchedByVersion($dependentRelease->getVersion()))
        ) {
            return ValidatedModIssueType::MISSING_DEPENDENCY;
        }

        if ($dependency->getType() === DependencyType::CONFLICT && $dependentRelease !== null) {
            return ValidatedModIssueType::CONFLICT;
        }

        return ValidatedModIssueType::NONE;
    }

    /**
     * @param array<ValidatedMod>|ValidatedMod[] $validatedMods
     * @return bool
     */
    public function areModsValid(array $validatedMods): bool
    {
        foreach ($validatedMods as $validatedMod) {
            if ($validatedMod->getIssueType() !== ValidatedModIssueType::NONE) {
                return false;
            }
        }
        return true;
    }
}
