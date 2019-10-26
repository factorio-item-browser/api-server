<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use FactorioItemBrowser\Api\Client\Entity\Entity as ClientEntity;
use FactorioItemBrowser\Api\Client\Entity\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Icon;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\IconService;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
        $imageIds = $this->fetchImageIds($namesByTypes);

        $clientIcons = $this->fetchIcons($imageIds);
        $filteredClientIcons = $this->filterRequestedIcons($clientIcons, $namesByTypes);
        $this->hydrateContentToIcons($filteredClientIcons);

        return $this->createResponse($filteredClientIcons);
    }

    /**
     * Fetches the image ids of the types and names.
     * @param NamesByTypes $namesByTypes
     * @return array|UuidInterface[]
     */
    protected function fetchImageIds(NamesByTypes $namesByTypes): array
    {
        $imageIds = $this->iconService->getImageIdsByTypesAndNames($namesByTypes);
        $allNamesByTypes = $this->iconService->getTypesAndNamesByImageIds($imageIds);
        return $this->iconService->getImageIdsByTypesAndNames($allNamesByTypes);
    }

    /**
     * Fetches the icons to the file hashes.
     * @param array|UuidInterface[] $imageIds
     * @return array|ClientIcon[]
     */
    protected function fetchIcons(array $imageIds): array
    {
        /* @var ClientIcon[] $result */
        $result = [];
        foreach ($this->iconService->getIconsByImageIds($imageIds) as $icon) {
            $imageId = $icon->getImage()->getId()->toString();
            if (!isset($result[$imageId])) {
                $result[$imageId] = $this->createClientIcon();
            }

            $entity = $this->createEntityForIcon($icon);
            $result[$imageId]->addEntity($entity);
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
     * @param Icon $icon
     * @return ClientEntity
     */
    protected function createEntityForIcon(Icon $icon): ClientEntity
    {
        $result = new ClientEntity();
        $result->setType($icon->getType())
               ->setName($icon->getName());
        return $result;
    }

    /**
     * Filters icons from the array which actually have been requested.
     * @param array|ClientIcon[] $clientIcons
     * @param NamesByTypes $namesByTypes
     * @return array|ClientIcon[]
     */
    protected function filterRequestedIcons(array $clientIcons, NamesByTypes $namesByTypes): array
    {
        return array_filter($clientIcons, function (ClientIcon $clientIcon) use ($namesByTypes): bool {
            return $this->wasIconRequested($clientIcon, $namesByTypes);
        });
    }

    /**
     * Checks whether the icon was initially requested.
     * @param ClientIcon $clientIcon
     * @param NamesByTypes $namesByTypes
     * @return bool
     */
    protected function wasIconRequested(ClientIcon $clientIcon, NamesByTypes $namesByTypes): bool
    {
        foreach ($clientIcon->getEntities() as $entity) {
            if ($namesByTypes->hasName($entity->getType(), $entity->getName())) {
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
        $iconFiles = $this->iconService->getImagesByIds(array_map(function (string $imageId): UuidInterface {
            return Uuid::fromString($imageId);
        }, array_keys($clientIcons)));

        foreach ($iconFiles as $iconFile) {
            $imageId = $iconFile->getId()->toString();
            if (isset($clientIcons[$imageId])) {
                $clientIcons[$imageId]->setContent($iconFile->getContents())
                                      ->setSize($iconFile->getSize());
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
