<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Database\Data\IconData;
use FactorioItemBrowser\Api\Database\Entity\IconFile;
use FactorioItemBrowser\Api\Database\Filter\DataFilter;
use FactorioItemBrowser\Api\Database\Repository\IconFileRepository;
use FactorioItemBrowser\Api\Database\Repository\IconRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Entity\NamesByTypes;

/**
 * The service handling the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconService
{
    /**
     * The data filter.
     * @var DataFilter
     */
    protected $dataFilter;

    /**
     * The repository of the icon files.
     * @var IconFileRepository
     */
    protected $iconFileRepository;

    /**
     * The repository of the icons.
     * @var IconRepository
     */
    protected $iconRepository;

    /**
     * The ids of the enabled mod combinations.
     * @var array|int[]
     */
    protected $enabledModCombinationIds = [];

    /**
     * IconService constructor.
     * @param DataFilter $dataFilter
     * @param IconFileRepository $iconFileRepository
     * @param IconRepository $iconRepository
     */
    public function __construct(
        DataFilter $dataFilter,
        IconFileRepository $iconFileRepository,
        IconRepository $iconRepository
    ) {
        $this->dataFilter = $dataFilter;
        $this->iconFileRepository = $iconFileRepository;
        $this->iconRepository = $iconRepository;
    }

    /**
     * Injects the authorization token into the service.
     * @param AuthorizationToken $authorizationToken
     */
    public function injectAuthorizationToken(AuthorizationToken $authorizationToken): void
    {
        $this->enabledModCombinationIds = $authorizationToken->getEnabledModCombinationIds();
    }

    /**
     * Returns the icon file hashes used by the specified entities.
     * @param NamesByTypes $namesByTypes
     * @return array|string[]
     */
    public function getHashesByTypesAndNames(NamesByTypes $namesByTypes): array
    {
        $iconData = $this->iconRepository->findDataByTypesAndNames(
            $namesByTypes->toArray(),
            $this->enabledModCombinationIds
        );

        $result = [];
        foreach ($iconData as $data) {
            $result[$data->getHash()] = true;
        }
        return array_keys($result);
    }

    /**
     * Returns types and names of icons which are using any of the specified hashes.
     * @param array|string[] $iconFileHashes
     * @return NamesByTypes
     */
    public function getTypesAndNamesByHashes(array $iconFileHashes): NamesByTypes
    {
        $result = new NamesByTypes();
        foreach ($this->getIconDataByHashes($iconFileHashes) as $data) {
            $result->addName($data->getType(), $data->getName());
        }
        return $result;
    }

    /**
     * Returns the icon data using the specified hashes.
     * @param array|string[] $iconFileHashes
     * @return array|IconData[]
     */
    public function getIconDataByHashes(array $iconFileHashes): array
    {
        $iconData = $this->iconRepository->findDataByHashes($iconFileHashes, $this->enabledModCombinationIds);;

        $result = [];
        foreach ($this->dataFilter->filter($iconData) as $data) {
            if ($data instanceof IconData) {
                $result[] = $data;
            }
        }
        return $result;
    }

    /**
     * Returns the icon files with the specified hashes.
     * @param array|string[] $iconFileHashes
     * @return array|IconFile[]
     */
    public function getIconFilesByHashes(array $iconFileHashes): array
    {
        return $this->iconFileRepository->findByHashes($iconFileHashes);
    }
}
