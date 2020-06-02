<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Entity\Mod as PortalMod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\MapperManager\Exception\MapperException;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Constant\Config;
use FactorioItemBrowser\Api\Server\Entity\CombinationUpdate;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobPriority;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException as ExportQueueClientException;
use Ramsey\Uuid\Uuid;

/**
 * The service detecting combinations to be updated.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationUpdateService
{
    protected const VALID_EXPORT_STATUS = [
        '',
        JobStatus::DONE,
        JobStatus::ERROR,
    ];

    protected CombinationRepository $combinationRepository;
    protected ExportQueueService $exportQueueService;
    protected ModPortalService $modPortalService;

    protected string $baseVersion;

    public function __construct(
        CombinationRepository $combinationRepository,
        ExportQueueService $exportQueueService,
        ModPortalService $modPortalService
    ) {
        $this->combinationRepository = $combinationRepository;
        $this->exportQueueService = $exportQueueService;
        $this->modPortalService = $modPortalService;

        $this->baseVersion = $this->fetchBaseVersion();
    }

    protected function fetchBaseVersion(): string
    {
        $combination = $this->combinationRepository->findById(Uuid::fromString(Config::DEFAULT_COMBINATION_ID));
        if ($combination !== null) {
            $baseMod = $combination->getMods()->first();
            if ($baseMod instanceof DatabaseMod && $baseMod->getName() === 'base') {
                return $baseMod->getVersion();
            }
        }

        return ''; // Cannot happen, base combination is always be present.
    }

    /**
     * @param Combination $combination
     * @return CombinationUpdate|null
     * @throws ClientException
     */
    public function checkCombination(Combination $combination): ?CombinationUpdate
    {
        $combinationUpdate = $this->createCombinationUpdate($combination);
        if ($combinationUpdate->secondsSinceLastUsage > $combinationUpdate->secondsSinceLastImport) {
            return null;
        }

        $portalMods = $this->modPortalService->requestModsOfCombination($combination);
        foreach ($combination->getMods() as $mod) {
            if ($mod->getName() === Constant::MOD_NAME_BASE) {
                if ($this->compareVersions($mod->getVersion(), $this->baseVersion) < 0) {
                    $combinationUpdate->hasBaseModUpdate = true;
                }
            } else {
                if (!isset($portalMods[$mod->getName()])) {
                    // Missing mod on mod portal. No need to attempt an update.
                    return null;
                }

                $release = $this->selectLatestRelease($portalMods[$mod->getName()]);
                if ($release === null) {
                    // Mod has no valid release. No need to attempt an update.
                    return null;
                }

                if ($this->compareVersions($mod->getVersion(), $release->getVersion()) < 0) {
                    ++$combinationUpdate->numberOfModUpdates;
                }
            }
        }

        if (!$combinationUpdate->hasBaseModUpdate && $combinationUpdate->numberOfModUpdates === 0) {
            return null;
        }

        return $combinationUpdate;
    }

    protected function createCombinationUpdate(Combination $combination): CombinationUpdate
    {
        $result = new CombinationUpdate();
        $result->combination = $combination;
        $result->secondsSinceLastImport = time() - $combination->getImportTime()->getTimestamp();
        $result->secondsSinceLastUsage = time() - $combination->getLastUsageTime()->getTimestamp();
        return $result;
    }

    protected function selectLatestRelease(PortalMod $mod): ?Release
    {
        /* @var Release|null $latestRelease */
        $latestRelease = null;
        foreach ($mod->getReleases() as $release) {
            if ($this->compareVersions($release->getInfoJson()->getFactorioVersion(), $this->baseVersion, 2) !== 0) {
                continue;
            }

            if (
                $latestRelease === null
                || $this->compareVersions($release->getVersion(), $latestRelease->getVersion()) > 0
            ) {
                $latestRelease = $release;
            }
        }

        return $latestRelease;
    }

    protected function compareVersions(string $leftVersion, string $rightVersion, int $numberOfParts = 3): int
    {
        $leftParts = $this->splitVersion($leftVersion, $numberOfParts);
        $rightParts = $this->splitVersion($rightVersion, $numberOfParts);

        $result = 0;
        for ($i = 0; $i < $numberOfParts && $result === 0; ++$i) {
            $result = $leftParts[$i] <=> $rightParts[$i];
        }

        return $result;
    }

    /**
     * @param string $version
     * @param int $numberOfParts
     * @return array<int>|int[]
     */
    protected function splitVersion(string $version, int $numberOfParts): array
    {
        $defaultParts = array_fill(0, $numberOfParts, 0);
        $parts = array_map('intval', explode('.', $version));
        return array_slice(array_merge($parts, $defaultParts), 0, $numberOfParts);
    }

    /**
     * @param array<CombinationUpdate>|CombinationUpdate[] $combinationUpdates
     * @throws MapperException
     */
    public function requestExportStatus(array $combinationUpdates): void
    {
        $requests = [];
        foreach ($combinationUpdates as $combinationUpdate) {
            $combinationId = $combinationUpdate->combination->getId();
            $requests[$combinationId->toString()] = $this->exportQueueService->createListRequest($combinationId);
        }

        $responses = $this->exportQueueService->executeListRequests($requests);
        foreach ($combinationUpdates as $combinationUpdate) {
            $combinationId = $combinationUpdate->combination->getId()->toString();
            if (isset($responses[$combinationId])) {
                $exportJob = $this->exportQueueService->mapResponseToExportJob($responses[$combinationId]);
                if ($exportJob !== null) {
                    $combinationUpdate->exportStatus = $exportJob->getStatus();
                }
            }
        }
    }

    /**
     * @param array<CombinationUpdate>|CombinationUpdate[] $combinationUpdates
     * @return array<CombinationUpdate>|CombinationUpdate[]
     */
    public function filter(array $combinationUpdates): array
    {
        return array_values(array_filter($combinationUpdates, function (CombinationUpdate $combinationUpdate): bool {
            return in_array($combinationUpdate->exportStatus, self::VALID_EXPORT_STATUS, true);
        }));
    }

    /**
     * @param array<CombinationUpdate>|CombinationUpdate[] $combinationUpdates
     * @return array<CombinationUpdate>|CombinationUpdate[]
     */
    public function sort(array $combinationUpdates): array
    {
        $count = count($combinationUpdates);
        $items = [];
        foreach ($combinationUpdates as $index => $combinationUpdate) {
            $score = $this->calculateScore($combinationUpdate);
            $items[$score * $count - $index] = $combinationUpdate;
        }

        ksort($items);
        return array_values(array_reverse($items));
    }

    protected function calculateScore(CombinationUpdate $combinationUpdate): int
    {
        $score = $combinationUpdate->secondsSinceLastImport / 86400
            - $combinationUpdate->secondsSinceLastUsage / 86400
            + $combinationUpdate->numberOfModUpdates
            + ($combinationUpdate->hasBaseModUpdate ? 10 : 0)
            + ($combinationUpdate->exportStatus === JobStatus::DONE ? 10 : 0);

        return (int) floor($score);
    }

    /**
     * @param array<CombinationUpdate>|CombinationUpdate[] $combinationUpdates
     * @throws ExportQueueClientException
     */
    public function triggerExports(array $combinationUpdates): void
    {
        foreach ($combinationUpdates as $combinationUpdate) {
            $this->exportQueueService->createExportForCombination($combinationUpdate->combination, JobPriority::SCRIPT);
        }
    }
}
