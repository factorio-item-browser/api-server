<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Client\ClientInterface;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Entity\Release;
use BluePsyduck\FactorioModPortalClient\Entity\Version;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\FullModRequest;
use BluePsyduck\FactorioModPortalClient\Utils\ModUtils;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;

/**
 * The service for accessing the Mod Portal API.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModPortalService
{
    protected ClientInterface $modPortalClient;

    /**
     * @var array<string,Mod|null>|Mod[]|null[]
     */
    protected array $requestedMods = [];

    public function __construct(ClientInterface $modPortalClient)
    {
        $this->modPortalClient = $modPortalClient;
    }

    /**
     * @param array<string> $modNames
     * @return array<string,Mod>|Mod[]
     * @throws ClientException
     */
    public function getMods(array $modNames): array
    {
        $missingModNames = $this->selectMissingModNames($modNames);
        $this->requestMods($missingModNames);

        return array_filter(array_intersect_key($this->requestedMods, array_flip($modNames)));
    }

    /**
     * @param Combination $combination
     * @return array<string,Mod>|Mod[]
     * @throws ClientException
     */
    public function getModsOfCombination(Combination $combination): array
    {
        $modNames = array_map(fn(DatabaseMod $mod) => $mod->getName(), $combination->getMods()->toArray());
        return $this->getMods($modNames);
    }

    /**
     * @param array<string> $modNames
     * @return array<string>
     */
    protected function selectMissingModNames(array $modNames): array
    {
        return array_values(array_diff($modNames, array_keys($this->requestedMods)));
    }

    /**
     * @param array<string>|string[] $modNames
     * @throws ClientException
     */
    protected function requestMods(array $modNames): void
    {
        $promises = [];
        foreach ($modNames as $modName) {
            $request = new FullModRequest();
            $request->setName($modName);

            $promises[$modName] = $this->modPortalClient->sendRequest($request);
        }

        $responses = Utils::settle($promises)->wait();
        foreach ($responses as $modName => $response) {
            if ($response['state'] === PromiseInterface::FULFILLED) {
                $this->requestedMods[$modName] = $response['value'];
            } else {
                $this->requestedMods[$modName] = null;
            }
        }
    }

    /**
     * @param array<string>|string[] $modNames
     * @param string $baseVersion
     * @return array<string,Release>|Release[]
     * @throws ClientException
     */
    public function getLatestReleases(array $modNames, string $baseVersion): array
    {
        $gameVersion = new Version($baseVersion);
        $latestReleases = [];
        foreach ($this->getMods($modNames) as $mod) {
            $latestReleases[$mod->getName()] = ModUtils::selectLatestRelease($mod, $gameVersion);
        }
        return array_filter($latestReleases);
    }
}
