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
            $iconData = $this->iconRepository->findHashesByTypesAndNames(
                $namesByTypes,
                $this->modService->getEnabledModCombinationIds()
            );
            foreach ($this->filterData($iconData, ['type', 'name']) as $data) {
                $hashes[intval($data['hash'])] = true;
            }
        }
        return array_keys($hashes);
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
            $icons = $this->iconRepository->findByHashes(
                $iconFileHashes,
                $this->modService->getEnabledModCombinationIds()
            );

            $namesByTypes = [];
            foreach ($icons as $icon) {
                $namesByTypes[$icon->getType()][] = $icon->getName();
            }
            $usedHashes = $this->getIconFileHashesByTypesAndNames($namesByTypes);

            foreach ($icons as $icon) {
                if (in_array($icon->getFile()->getHash(), $usedHashes)) {
                    $result[] = $icon;
                }
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