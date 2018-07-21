<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\Icon;
use FactorioItemBrowser\Api\Server\Database\Entity\IconFile;
use FactorioItemBrowser\Api\Server\Database\Repository\IconFileRepository;
use FactorioItemBrowser\Api\Server\Database\Repository\IconRepository;

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
     * @return array|int[]
     */
    public function getIconFileHashesByTypesAndNames(array $namesByTypes)
    {
        $hashes = [];
        if (count($namesByTypes) > 0) {
            $iconData = $this->iconRepository->findHashDataByTypesAndNames(
                $namesByTypes,
                $this->modService->getEnabledModCombinationIds()
            );
            foreach ($iconData as $data) {
                $hashes[bin2hex($data['hash'])] = true;
            }
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
        $result = [];
        if (count($iconFileHashes) > 0) {
            $iconData = $this->iconRepository->findIdDataByHashes(
                $iconFileHashes,
                $this->modService->getEnabledModCombinationIds()
            );
            foreach ($iconData as $data) {
                $result[$data['type']][] = $data['name'];
            }
        }
        return $result;
    }

    /**
     * Returns the icons using the specified hashes.
     * @param array $iconFileHashes
     * @return array|Icon[]
     */
    public function getIconsByHashes(array $iconFileHashes): array
    {
        $result = [];
        if (count($iconFileHashes) > 0) {
            $iconIds = [];
            $iconData = $this->iconRepository->findIdDataByHashes(
                $iconFileHashes,
                $this->modService->getEnabledModCombinationIds()
            );
            foreach ($this->filterData($iconData, ['type', 'name']) as $data) {
                $iconIds[] = intval($data['id']);
            }

            if (count($iconIds) > 0) {
                $result = $this->iconRepository->findByIds($iconIds);
            }
        }
        return $result;
    }

    /**
     * Returns the icon files with the specified hashes.
     * @param array|int[] $iconFileHashes
     * @return array|IconFile[]
     */
    public function getIconFilesByHashes(array $iconFileHashes): array
    {
        $result = [];
        if (count($iconFileHashes) > 0) {
            foreach ($this->iconFileRepository->findByHashes($iconFileHashes) as $iconFile) {
                $result[$iconFile->getHash()] = $iconFile;
            }
        }
        return $result;
    }

    /**
     * Removes any orphaned icon files, i.e. icon files no longer used by any icon.
     * @return $this
     */
    public function removeOrphans()
    {
        $this->iconFileRepository->removeOrphans();
        return $this;
    }
}
