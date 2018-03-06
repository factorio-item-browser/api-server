<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Entity\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Entity\IconEntity as ClientIconEntity;
use FactorioItemBrowser\Api\Server\Database\Service\IconService;

/**
 * The handler of the /generic/icon request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericIconHandler extends AbstractGenericHandler
{
    /**
     * The database icon service.
     * @var IconService
     */
    protected $iconService;

    /**
     * Initializes the request handler.
     * @param IconService $iconService
     */
    public function __construct(IconService $iconService)
    {
        $this->iconService = $iconService;
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $namesByTypes = $this->getEntityNamesByType($requestData);
        $iconFileHashes = $this->iconService->getIconFileHashesByTypesAndNames($namesByTypes);

        $clientIcons = $this->prepareClientIcons($iconFileHashes);
        foreach ($this->iconService->getIconsByHashes($iconFileHashes) as $databaseIcon) {
            $hash = $databaseIcon->getFile()->getHash();
            if (isset($clientIcons[$hash])) {
                $clientIconEntity = new ClientIconEntity();
                $clientIconEntity
                    ->setType($databaseIcon->getType())
                    ->setName($databaseIcon->getName());
                $clientIcons[$hash]->addEntity($clientIconEntity);
            }
        }

        return [
            'icons' => array_values($clientIcons),
        ];
    }

    /**
     * Prepares the client icons for the specified hashes.
     * @param array|int[] $iconFileHashes
     * @return array|ClientIcon[]
     */
    protected function prepareClientIcons(array $iconFileHashes): array
    {
        $result = [];
        foreach ($this->iconService->getIconFilesByHashes($iconFileHashes) as $iconFile) {
            $clientIcon = new ClientIcon();
            $clientIcon->setContent(base64_encode($iconFile->getImage()));
            $result[$iconFile->getHash()] = $clientIcon;
        }
        return $result;
    }
}