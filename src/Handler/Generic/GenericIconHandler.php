<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Entity\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Entity\IconEntity as ClientIconEntity;
use FactorioItemBrowser\Api\Server\Database\Entity\Icon as DatabaseIcon;
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

        $groupedDatabaseIcons = $this->iconService->getIconsByHashes($iconFileHashes);
        $clientIcons = [];
        foreach ($groupedDatabaseIcons as $databaseIcons) {
            $clientIcon = null;
            foreach ($databaseIcons as $databaseIcon) {
                /* @var DatabaseIcon $databaseIcon */
                if (is_null($clientIcon)) {
                    $clientIcon = new ClientIcon();
                    $clientIcon->setContent(base64_encode($databaseIcon->getFile()->getImage()));
                }
                $clientIconEntity = new ClientIconEntity();
                $clientIconEntity
                    ->setType($databaseIcon->getType())
                    ->setName($databaseIcon->getName());
                $clientIcon->addEntity($clientIconEntity);
            }
            $clientIcons[] = $clientIcon;
        }

        return [
            'icons' => $clientIcons,
        ];
    }
}