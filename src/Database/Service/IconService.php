<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\Icon;
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
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->iconRepository = $entityManager->getRepository(Icon::class);
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
     * Returns the icons using the specified hashes, grouped by the hashes.
     * @param array $iconFileHashes
     * @return array|Icon[][]
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
                    $result[$icon->getFile()->getHash()][] = $icon;
                }
            }
        }
        return $result;
    }
}