<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\MapperManager\Exception\MapperException;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Server\Entity\CombinationUpdate;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobPriority;
use FactorioItemBrowser\ExportQueue\Client\Constant\JobStatus;
use FactorioItemBrowser\ExportQueue\Client\Exception\ClientException as ExportQueueClientException;

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

    protected CombinationValidationService $combinationValidationService;
    protected ExportQueueService $exportQueueService;
    protected ModPortalService $modPortalService;

    public function __construct(
        CombinationValidationService $combinationValidationService,
        ExportQueueService $exportQueueService,
        ModPortalService $modPortalService
    ) {
        $this->combinationValidationService = $combinationValidationService;
        $this->exportQueueService = $exportQueueService;
        $this->modPortalService = $modPortalService;
    }

    /**
     * @param Combination $combination
     * @return CombinationUpdate|null
     * @throws ApiServerException
     * @throws ClientException
     */
    public function checkCombination(Combination $combination): ?CombinationUpdate
    {
        $combinationUpdate = $this->createCombinationUpdate($combination);
        if ($combinationUpdate->secondsSinceLastUsage > $combinationUpdate->secondsSinceLastImport) {
            // Combination was not used since the last update. So why bother?
            return null;
        }

        $modNames = array_map(fn (Mod $mod): string => $mod->getName(), $combination->getMods()->toArray());
        $validatedMods = $this->combinationValidationService->validate($modNames);
        if (!$this->combinationValidationService->areModsValid($validatedMods)) {
            // The combination is (no longer?) valid. We cannot update it anymore.
            return null;
        }

        foreach ($combination->getMods() as $mod) {
            $validatedMod = $validatedMods[$mod->getName()];

            $modVersion = new Version($mod->getVersion());
            $validatedVersion = new Version($validatedMod->getVersion());
            if ($validatedVersion->compareTo($modVersion) > 0) {
                ++$combinationUpdate->numberOfModUpdates;

                if ($mod->getName() === Constant::MOD_NAME_BASE) {
                    $combinationUpdate->hasBaseModUpdate = true;
                }
            }
        }

        if ($combinationUpdate->numberOfModUpdates === 0) {
            // None of the mods have an actual update. So we are already up-to-date for the combination.
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

    /**
     * @param array<CombinationUpdate>|CombinationUpdate[] $combinationUpdates
     * @throws MapperException
     * @throws ExportQueueClientException
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
