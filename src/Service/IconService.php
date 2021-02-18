<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Icon;
use FactorioItemBrowser\Api\Database\Entity\IconImage;
use FactorioItemBrowser\Api\Database\Repository\IconImageRepository;
use FactorioItemBrowser\Api\Database\Repository\IconRepository;
use Ramsey\Uuid\UuidInterface;

/**
 * The service handling the icons.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class IconService
{
    private IconImageRepository $iconImageRepository;
    private IconRepository $iconRepository;
    private UuidInterface $combinationId;

    public function __construct(
        IconImageRepository $iconImageRepository,
        IconRepository $iconRepository
    ) {
        $this->iconImageRepository = $iconImageRepository;
        $this->iconRepository = $iconRepository;
    }

    public function setCombinationId(UuidInterface $combinationId): void
    {
        $this->combinationId = $combinationId;
    }

    /**
     * Returns the image ids used by the specified entities.
     * @param NamesByTypes $namesByTypes
     * @return array<UuidInterface>
     */
    public function getImageIdsByTypesAndNames(NamesByTypes $namesByTypes): array
    {
        $icons = $this->iconRepository->findByTypesAndNames(
            $this->combinationId,
            $namesByTypes
        );

        $result = [];
        foreach ($icons as $icon) {
            $result[$icon->getImage()->getId()->toString()] = $icon->getImage()->getId();
        }
        return array_values($result);
    }

    /**
     * Returns types and names of icons which are using any of the specified hashes.
     * @param array<UuidInterface> $imageIds
     * @return NamesByTypes
     */
    public function getTypesAndNamesByImageIds(array $imageIds): NamesByTypes
    {
        $result = new NamesByTypes();
        foreach ($this->getIconsByImageIds($imageIds) as $data) {
            $result->addName($data->getType(), $data->getName());
        }
        return $result;
    }

    /**
     * Returns the icon data using the specified hashes.
     * @param array<UuidInterface> $imageIds
     * @return array<Icon>
     */
    public function getIconsByImageIds(array $imageIds): array
    {
        return $this->iconRepository->findByImageIds($this->combinationId, $imageIds);
    }

    /**
     * Returns the icon files with the specified hashes.
     * @param array<UuidInterface> $imageIds
     * @return array<IconImage>
     */
    public function getImagesByIds(array $imageIds): array
    {
        return $this->iconImageRepository->findByIds($imageIds);
    }
}
