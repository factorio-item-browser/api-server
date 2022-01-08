<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The handler of the /generic/details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericDetailsHandler implements RequestHandlerInterface
{
    use TypeAndNameFromEntityExtractorTrait;

    public function __construct(
        protected readonly ItemRepository $itemRepository,
        protected readonly MachineRepository $machineRepository,
        protected readonly MapperManagerInterface $mapperManager,
        protected readonly RecipeRepository $recipeRepository,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var GenericDetailsRequest $clientRequest */
        $clientRequest = $request->getParsedBody();
        $combinationId = Uuid::fromString($clientRequest->combinationId);
        $namesByTypes = $this->extractTypesAndNames($clientRequest->entities);

        $entities = array_values(array_merge(
            $this->processItems($combinationId, $namesByTypes),
            $this->processMachines($combinationId, $namesByTypes),
            $this->processRecipes($combinationId, $namesByTypes),
        ));

        $response = new GenericDetailsResponse();
        $response->entities = $entities;

        return new ClientResponse($response);
    }

    /**
     * @return array<string, GenericEntity>
     */
    protected function processItems(UuidInterface $combinationId, NamesByTypes $namesByTypes): array
    {
        $items = $this->itemRepository->findByTypesAndNames($combinationId, $namesByTypes);
        return $this->mapObjectsToEntities($items);
    }

    /**
     * @return array<string, GenericEntity>
     */
    protected function processMachines(UuidInterface $combinationId, NamesByTypes $namesByTypes): array
    {
        $machines = $this->machineRepository->findByNames(
            $combinationId,
            $namesByTypes->getNames(EntityType::MACHINE)
        );
        return $this->mapObjectsToEntities($machines);
    }

    /**
     * @return array<string, GenericEntity>
     */
    protected function processRecipes(UuidInterface $combinationId, NamesByTypes $namesByTypes): array
    {
        $recipes = $this->recipeRepository->findDataByNames(
            $combinationId,
            $namesByTypes->getNames(EntityType::RECIPE)
        );
        return $this->mapObjectsToEntities($recipes);
    }

    /**
     * @param array<object> $objects
     * @return array<string, GenericEntity>
     */
    protected function mapObjectsToEntities(array $objects): array
    {
        $result = [];
        foreach ($objects as $object) {
            $entity = $this->mapperManager->map($object, new GenericEntity());
            $result["{$entity->type}|{$entity->name}"] = $entity;
        }
        return $result;
    }
}
