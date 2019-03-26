<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use FactorioItemBrowser\Api\Client\Entity\Entity as ClientEntity;
use FactorioItemBrowser\Api\Client\Entity\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Data\IconData;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\IconService;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;

/**
 * The handler of the /generic/icon request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericIconHandler extends AbstractRequestHandler
{
    use TypeAndNameFromEntityExtractorTrait;

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
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return GenericIconRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param GenericIconRequest $request
     * @return ResponseInterface
     */
    protected function handleRequest($request): ResponseInterface
    {
        $this->iconService->injectAuthorizationToken($this->getAuthorizationToken());

        $namesByTypes = $this->extractTypesAndNames($request->getEntities());
        $iconFileHashes = $this->fetchIconFileHashes($namesByTypes);

        $clientIcons = $this->fetchIcons($iconFileHashes);
        $filteredClientIcons = $this->filterRequestedIcons($clientIcons, $namesByTypes);
        $this->hydrateContentToIcons($filteredClientIcons);

        return $this->createResponse($filteredClientIcons);
    }

    /**
     * Fetches the icon file hashes of the types and names.
     * @param array|string[][] $namesByTypes
     * @return array|string[]
     */
    protected function fetchIconFileHashes(array $namesByTypes): array
    {
        $iconFileHashes = $this->iconService->getIconFileHashesByTypesAndNames($namesByTypes);
        $allNamesByTypes = $this->iconService->getAllTypesAndNamesByHashes($iconFileHashes);
        return $this->iconService->getIconFileHashesByTypesAndNames($allNamesByTypes);
    }

    /**
     * Fetches the icons to the file hashes.
     * @param array $iconFileHashes
     * @return array|ClientIcon[]
     */
    protected function fetchIcons(array $iconFileHashes): array
    {
        /* @var ClientIcon[] $result */
        $result = [];
        foreach ($this->iconService->getIconDataByHashes($iconFileHashes) as $iconData) {
            $hash = $iconData->getHash();
            if (!isset($result[$hash])) {
                $result[$hash] = $this->createClientIcon();
            }

            $entity = $this->createEntityForIconData($iconData);
            $result[$hash]->addEntity($entity);
        }
        return $result;
    }

    /**
     * Creates a client icon.
     * @return ClientIcon
     */
    protected function createClientIcon(): ClientIcon
    {
        return new ClientIcon();
    }

    /**
     * Creates an entity to assign to an icon.
     * @param IconData $iconData
     * @return ClientEntity
     */
    protected function createEntityForIconData(IconData $iconData): ClientEntity
    {
        $result= new ClientEntity();
        $result->setType($iconData->getType())
               ->setName($iconData->getName());
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
        foreach ($clientIcon->getEntities() as $entity) {
            if (in_array($entity->getName(), $namesByTypes[$entity->getType()] ?? [], true)) {
                return true;
            }
        }
        return false;
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
                $clientIcons[$iconFileHash]->setContent($iconFile->getImage());
            }
        }
    }

    /**
     * Creates the final response of the request.
     * @param array|ClientIcon[] $clientIcons
     * @return GenericIconResponse
     */
    protected function createResponse(array $clientIcons): GenericIconResponse
    {
        $result = new GenericIconResponse();
        $result->setIcons($clientIcons);
        return $result;
    }
}
