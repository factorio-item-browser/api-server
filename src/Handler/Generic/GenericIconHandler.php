<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use FactorioItemBrowser\Api\Client\Transfer\Entity as ClientEntity;
use FactorioItemBrowser\Api\Client\Transfer\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\IconService;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The handler of the /generic/icon request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericIconHandler implements RequestHandlerInterface
{
    use TypeAndNameFromEntityExtractorTrait;

    protected IconService $iconService;

    public function __construct(IconService $iconService)
    {
        $this->iconService = $iconService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var GenericIconRequest $clientRequest */
        $clientRequest = $request->getParsedBody();

        $combinationId = Uuid::fromString($clientRequest->combinationId);
        $namesByTypes = $this->extractTypesAndNames($clientRequest->entities);

        $this->iconService->setCombinationId($combinationId);
        $imageIds = $this->fetchImageIds($namesByTypes);
        $icons = $this->fetchIcons($imageIds);
        $filteredIcons = $this->filterRequestedIcons($icons, $namesByTypes);
        $this->hydrateContentToIcons($filteredIcons);

        $response = new GenericIconResponse();
        $response->icons = $filteredIcons;
        return new ClientResponse($response);
    }

    /**
     * @param NamesByTypes $namesByTypes
     * @return array<UuidInterface>
     */
    protected function fetchImageIds(NamesByTypes $namesByTypes): array
    {
        $imageIds = $this->iconService->getImageIdsByTypesAndNames($namesByTypes);
        $allNamesByTypes = $this->iconService->getTypesAndNamesByImageIds($imageIds);
        return $this->iconService->getImageIdsByTypesAndNames($allNamesByTypes);
    }

    /**
     * @param array<UuidInterface> $imageIds
     * @return array<string, ClientIcon>
     */
    protected function fetchIcons(array $imageIds): array
    {
        $icons = [];
        foreach ($this->iconService->getIconsByImageIds($imageIds) as $icon) {
            $imageId = $icon->getImage()->getId()->toString();
            if (!isset($icons[$imageId])) {
                $icons[$imageId] = new ClientIcon();
            }

            $entity = new ClientEntity();
            $entity->type = $icon->getType();
            $entity->name = $icon->getName();
            $icons[$imageId]->entities[] = $entity;
        }
        return $icons;
    }

    /**
     * @param array<string, ClientIcon> $icons
     * @param NamesByTypes $namesByTypes
     * @return array<string, ClientIcon>
     */
    protected function filterRequestedIcons(array $icons, NamesByTypes $namesByTypes): array
    {
        return array_filter($icons, function (ClientIcon $icon) use ($namesByTypes): bool {
            foreach ($icon->entities as $entity) {
                if ($namesByTypes->hasName($entity->type, $entity->name)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * @param array<string, ClientIcon> $icons
     */
    protected function hydrateContentToIcons(array $icons): void
    {
        $iconIds = array_map(fn($imageId) => Uuid::fromString($imageId), array_keys($icons));
        foreach ($this->iconService->getImagesByIds($iconIds) as $iconImage) {
            $imageId = $iconImage->getId()->toString();
            if (isset($icons[$imageId])) {
                $icons[$imageId]->content = $iconImage->getContents();
                $icons[$imageId]->size = $iconImage->getSize();
            }
        }
    }
}
