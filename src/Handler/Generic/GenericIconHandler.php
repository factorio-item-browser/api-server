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
        $iconFileHashes = $this->getIconFileHashesByTypesAndNames($namesByTypes);

        $clientIcons = $this->prepareClientIcons($iconFileHashes);
        $this->fetchEntitiesToIcons($clientIcons);

        $filteredClientIcons = $this->filterRequestedIcons($clientIcons, $namesByTypes);
        $this->hydrateContentToIcons($filteredClientIcons);

        return [
            'icons' => array_values($filteredClientIcons),
        ];
    }

    /**
     * Returns the icon hashes from the specified types and names.
     * @param array|string[][] $namesByTypes
     * @return array|string[]
     */
    protected function getIconFileHashesByTypesAndNames(array $namesByTypes): array
    {
        $iconFileHashes = $this->iconService->getIconFileHashesByTypesAndNames($namesByTypes);
        $allNamesByTypes = $this->iconService->getAllTypesAndNamesByHashes($iconFileHashes);
        return $this->iconService->getIconFileHashesByTypesAndNames($allNamesByTypes);
    }

    /**
     * Prepares the client icons for the specified hashes.
     * @param array|string[] $iconFileHashes
     * @return array|ClientIcon[]
     */
    protected function prepareClientIcons(array $iconFileHashes): array
    {
        $result = [];
        foreach ($iconFileHashes as $iconFileHash) {
            $result[$iconFileHash] = new ClientIcon();
        }
        return $result;
    }

    /**
     * Fetches the entities of the specified icons.
     * @param array|ClientIcon[] $clientIcons
     */
    protected function fetchEntitiesToIcons(array $clientIcons): void
    {
        $databaseIcons = $this->iconService->getIconsByHashes(array_keys($clientIcons));
        foreach ($databaseIcons as $databaseIcon) {
            $iconFileHash = $databaseIcon->getFile()->getHash();
            if (isset($clientIcons[$iconFileHash])) {
                $clientIcons[$iconFileHash]->addEntity(
                    $this->createClientIconEntity($databaseIcon->getType(), $databaseIcon->getName())
                );
            }
        }
    }

    /**
     * Creates a client icon entity.
     * @param string $type
     * @param string $name
     * @return ClientIconEntity
     */
    protected function createClientIconEntity(string $type, string $name): ClientIconEntity
    {
        $result= new ClientIconEntity();
        $result->setType($type)
               ->setName($name);
        return $result;
    }

    /**
     * Filters icons from the array which actually have been requested.
     * @param array|ClientIcon[] $clientIcons
     * @param array|string[][] $namesByTypes
     * @return array|ClientIcon[]
     */
    protected function filterRequestedIcons(array $clientIcons, array $namesByTypes): array
    {
        return array_filter($clientIcons, function (ClientIcon $clientIcon) use ($namesByTypes): bool {
            return $this->wasIconRequested($clientIcon, $namesByTypes);
        });
    }

    /**
     * Checks whether the icon was initially requested.
     * @param ClientIcon $clientIcon
     * @param array|string[][] $namesByTypes
     * @return bool
     */
    protected function wasIconRequested(ClientIcon $clientIcon, array $namesByTypes): bool
    {
        $result = false;
        foreach ($clientIcon->getEntities() as $entity) {
            if (isset($namesByTypes[$entity->getType()])
                && in_array($entity->getName(), $namesByTypes[$entity->getType()], true)
            ) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Hydrates the contents into the icons.
     * @param array|ClientIcon[] $clientIcons
     */
    protected function hydrateContentToIcons(array $clientIcons): void
    {
        $iconFiles = $this->iconService->getIconFilesByHashes(array_keys($clientIcons));
        foreach ($iconFiles as $iconFile) {
            $iconFileHash = $iconFile->getHash();
            if (isset($clientIcons[$iconFileHash])) {
                $clientIcons[$iconFileHash]->setContent(base64_encode($iconFile->getImage()));
            }
        }
    }
}
