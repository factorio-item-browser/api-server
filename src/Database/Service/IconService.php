<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Data\IconData;
use FactorioItemBrowser\Api\Database\Entity\Icon;
use FactorioItemBrowser\Api\Database\Entity\IconFile;
use FactorioItemBrowser\Api\Database\Repository\IconFileRepository;
use FactorioItemBrowser\Api\Database\Repository\IconRepository;

/**
 * The service class of the icon database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconService extends AbstractModsAwareService
{
    /**
     * The repository of the icons.
     * @var IconRepository
     */
    protected $iconRepository;

    /**
     * The repository of the icon files.
     * @var IconFileRepository
     */
    protected $iconFileRepository;

    /**
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->iconRepository = $entityManager->getRepository(Icon::class);
        $this->iconFileRepository = $entityManager->getRepository(IconFile::class);
        return $this;
    }

    /**
     * Returns the icon file hashes used by the specified entities.
     * @param array|string[][] $namesByTypes
     * @return array|string[]
     */
    public function getIconFileHashesByTypesAndNames(array $namesByTypes)
    {
        $iconData = $this->iconRepository->findDataByTypesAndNames(
            $namesByTypes,
            $this->modService->getEnabledModCombinationIds()
        );

        $hashes = [];
        foreach ($iconData as $data) {
            $hashes[$data->getHash()] = true;
        }
        return array_keys($hashes);
    }

    /**
     * Returns all types and names of icons which are using any of the specified hashes.
     * @param array|string[] $iconFileHashes
     * @return array|string[][]
     */
    public function getAllTypesAndNamesByHashes(array $iconFileHashes): array
    {
        $iconData = $this->iconRepository->findDataByHashes(
            $iconFileHashes,
            $this->modService->getEnabledModCombinationIds()
        );

        $result = [];
        foreach ($iconData as $data) {
            $result[$data->getType()][] = $data->getName();
        }
        return $result;
    }

    /**
     * Returns the icons using the specified hashes.
     * @param array|string[] $iconFileHashes
     * @return array|Icon[]
     */
    public function getIconsByHashes(array $iconFileHashes): array
    {
        $iconData = $this->iconRepository->findDataByHashes(
            $iconFileHashes,
            $this->modService->getEnabledModCombinationIds()
        );

        $iconIds = [];
        foreach ($this->filterData($iconData) as $data) {
            if ($data instanceof IconData) {
                $iconIds[] = $data->getId();
            }
        }

        return $this->iconRepository->findByIds($iconIds);
    }

    /**
     * Returns the icon files with the specified hashes.
     * @param array|string[] $iconFileHashes
     * @return array|IconFile[]
     */
    public function getIconFilesByHashes(array $iconFileHashes): array
    {
        $result = [];
        foreach ($this->iconFileRepository->findByHashes($iconFileHashes) as $iconFile) {
            $result[$iconFile->getHash()] = $iconFile;
        }
        return $result;
    }
}
