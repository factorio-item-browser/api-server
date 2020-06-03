<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use BluePsyduck\FactorioModPortalClient\Client\Facade;
use BluePsyduck\FactorioModPortalClient\Entity\Mod;
use BluePsyduck\FactorioModPortalClient\Exception\ClientException;
use BluePsyduck\FactorioModPortalClient\Request\ModListRequest;
use FactorioItemBrowser\Api\Database\Entity\Combination;

/**
 * The service for accessing the Mod Portal API.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ModPortalService
{
    protected Facade $modPortalFacade;

    /**
     * @var array<string,Mod>|Mod[]
     */
    protected array $requestedMods = [];

    public function __construct(Facade $modPortalFacade)
    {
        $this->modPortalFacade = $modPortalFacade;
    }

    /**
     * @param Combination $combination
     * @return array<Mod>|Mod[]
     * @throws ClientException
     */
    public function requestModsOfCombination(Combination $combination): array
    {
        $modNames = [];
        foreach ($combination->getMods() as $mod) {
            $modNames[] = $mod->getName();
        }

        return $this->requestMods($modNames);
    }

    /**
     * @param array<string>|string[] $modNames
     * @return array<Mod>|Mod[]
     * @throws ClientException
     */
    public function requestMods(array $modNames): array
    {
        $this->requestMissingMods($modNames);
        return array_intersect_key($this->requestedMods, array_flip($modNames));
    }

    /**
     * @param array<string>|string[] $modNames
     * @throws ClientException
     */
    protected function requestMissingMods(array $modNames): void
    {
        $missingModNames = array_values(array_diff($modNames, array_keys($this->requestedMods)));
        if (count($missingModNames) === 0) {
            return;
        }

        $request = new ModListRequest();
        $request->setNameList($missingModNames)
                ->setPageSize(count($missingModNames));

        $response = $this->modPortalFacade->getModList($request);
        foreach ($response->getResults() as $mod) {
            $this->requestedMods[$mod->getName()] = $mod;
        }
    }
}
