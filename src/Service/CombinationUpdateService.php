<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Exception\RejectedCombinationUpdateException;
use FactorioItemBrowser\CombinationApi\Client\ClientInterface;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobPriority;
use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Request\Combination\ValidateRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\CreateRequest;
use FactorioItemBrowser\CombinationApi\Client\Response\Combination\ValidateResponse;
use FactorioItemBrowser\Common\Constant\Constant;
use FactorioItemBrowser\Common\Constant\Defaults;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The service handling the updates of combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationUpdateService
{
    private CombinationRepository $combinationRepository;
    private ClientInterface $combinationApiClient;
    private string $baseVersion = '';

    public function __construct(
        CombinationRepository $combinationRepository,
        ClientInterface $combinationApiClient,
    ) {
        $this->combinationRepository = $combinationRepository;
        $this->combinationApiClient = $combinationApiClient;
    }

    private function getBaseVersion(): string
    {
        if ($this->baseVersion === '') {
            $combination = $this->combinationRepository->findById(Uuid::fromString(Defaults::COMBINATION_ID));
            if ($combination !== null) {
                foreach ($combination->getMods() as $mod) {
                    if ($mod->getName() === Constant::MOD_NAME_BASE) {
                        $this->baseVersion = $mod->getVersion();
                        break;
                    }
                }
            }
        }
        return $this->baseVersion;
    }

    /**
     * Checks whether the combination actually requires an update.
     *
     * If the combination requires an update, the returned promise will fulfill to a UUID, which represents the
     * update hash for the combination. If the combination does not require an update, the returned promise will be
     * rejected with an exception containing the reason.
     *
     * @param Combination $combination
     * @return PromiseInterface
     */
    public function checkCombination(Combination $combination): PromiseInterface
    {
        $validateRequest = new ValidateRequest();
        $validateRequest->combinationId = $combination->getId()->toString();
        $validateRequest->factorioVersion = $this->getBaseVersion();

        try {
            $promise = $this->combinationApiClient->sendRequest($validateRequest);
        } catch (ClientException $e) {
            return new RejectedPromise($e);
        }

        return $promise->then(function (ValidateResponse $response) use ($combination): UuidInterface {
            return $this->handleValidateResponse($combination, $response);
        });
    }

    /**
     * @param Combination $combination
     * @param ValidateResponse $validateResponse
     * @return UuidInterface
     * @throws RejectedCombinationUpdateException
     */
    private function handleValidateResponse(
        Combination $combination,
        ValidateResponse $validateResponse,
    ): UuidInterface {
        if (!$validateResponse->isValid) {
            throw new RejectedCombinationUpdateException('The combination is not valid.');
        }

        $newModVersions = [];
        foreach ($validateResponse->mods as $mod) {
            $newModVersions[$mod->name] = $mod->version;
        }

        $numberOfModUpdates = 0;
        foreach ($combination->getMods() as $mod) {
            if (!isset($newModVersions[$mod->getName()])) {
                continue;
            }

            if ($this->convertVersion($newModVersions[$mod->getName()]) > $this->convertVersion($mod->getVersion())) {
                ++$numberOfModUpdates;
            }
        }
        if ($numberOfModUpdates === 0) {
            throw new RejectedCombinationUpdateException('There are no mods to update.');
        }

        $updateHash = Uuid::fromString(hash('md5', (string) json_encode($newModVersions)));
        if ($updateHash->equals($combination->getLastUpdateHash())) {
            throw new RejectedCombinationUpdateException('The update hash is identical to the last attempt.');
        }
        return $updateHash;
    }

    /**
     * Converts the version string to an integer for easier comparison.
     * @param string $version
     * @return int
     */
    private function convertVersion(string $version): int
    {
        $parts = array_map('intval', explode('.', $version));
        return ($parts[0] ?? 0) * 10000 + ($parts[1] ?? 0) * 100 + ($parts[2] ?? 0);
    }

    public function triggerUpdate(Combination $combination): void
    {
        $request = new CreateRequest();
        $request->combinationId = $combination->getId()->toString();
        $request->priority = JobPriority::AUTO_UPDATE;

        try {
            $this->combinationApiClient->sendRequest($request)->wait();
        } catch (ClientException) {
            // Oh no! Anyway...
        }
    }
}
