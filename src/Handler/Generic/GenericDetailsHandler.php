<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;
use FactorioItemBrowser\Common\Constant\EntityType;

/**
 * The handler of the /generic/details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericDetailsHandler extends AbstractRequestHandler
{
    use TypeAndNameFromEntityExtractorTrait;

    /**
     * The item repository.
     * @var ItemRepository
     */
    protected $itemRepository;

    /**
     * The machine repository.
     * @var MachineRepository
     */
    protected $machineRepository;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The recipe repository.
     * @var RecipeRepository
     */
    protected $recipeRepository;

    /**
     * Initializes the request handler.
     * @param ItemRepository $itemRepository
     * @param MachineRepository $machineRepository
     * @param MapperManagerInterface $mapperManager
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(
        ItemRepository $itemRepository,
        MachineRepository $machineRepository,
        MapperManagerInterface $mapperManager,
        RecipeRepository $recipeRepository
    ) {
        $this->itemRepository = $itemRepository;
        $this->machineRepository = $machineRepository;
        $this->mapperManager = $mapperManager;
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return GenericDetailsRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param GenericDetailsRequest $request
     * @return ResponseInterface
     * @throws MapperException
     */
    protected function handleRequest($request): ResponseInterface
    {
        $namesByTypes = $this->extractTypesAndNames($request->getEntities());
        $authorizationToken = $this->getAuthorizationToken();
        $entities = $this->process($namesByTypes, $authorizationToken);
        return $this->createResponse($entities);
    }

    /**
     * Processes the types and names.
     * @param NamesByTypes $namesByTypes
     * @param AuthorizationToken $authorizationToken
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function process(NamesByTypes $namesByTypes, AuthorizationToken $authorizationToken): array
    {
        return array_values(array_merge(
            $this->processItems($namesByTypes, $authorizationToken),
            $this->processMachines($namesByTypes, $authorizationToken),
            $this->processRecipes($namesByTypes, $authorizationToken)
        ));
    }

    /**
     * Processes the items.
     * @param NamesByTypes $namesByTypes
     * @param AuthorizationToken $authorizationToken
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function processItems(NamesByTypes $namesByTypes, AuthorizationToken $authorizationToken): array
    {
        $items = $this->itemRepository->findByTypesAndNames(
            $authorizationToken->getCombinationId(),
            $namesByTypes
        );
        return $this->mapObjectsToEntities($items);
    }

    /**
     * Processes the machines.
     * @param NamesByTypes $namesByTypes
     * @param AuthorizationToken $authorizationToken
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function processMachines(NamesByTypes $namesByTypes, AuthorizationToken $authorizationToken): array
    {
        $machines = $this->machineRepository->findByNames(
            $authorizationToken->getCombinationId(),
            $namesByTypes->getNames(EntityType::MACHINE)
        );
        return $this->mapObjectsToEntities($machines);
    }

    /**
     * Processes the recipes.
     * @param NamesByTypes $namesByTypes
     * @param AuthorizationToken $authorizationToken
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function processRecipes(NamesByTypes $namesByTypes, AuthorizationToken $authorizationToken): array
    {
        $recipes = $this->recipeRepository->findDataByNames(
            $authorizationToken->getCombinationId(),
            $namesByTypes->getNames(EntityType::RECIPE)
        );
        return $this->mapObjectsToEntities($recipes);
    }

    /**
     * Maps an array of objects to client entities.
     * @param array|object[] $objects
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function mapObjectsToEntities(array $objects): array
    {
        $result = [];
        foreach ($objects as $object) {
            $entity = new GenericEntity();
            $this->mapperManager->map($object, $entity);
            $result[$this->getEntityKey($entity)] = $entity;
        }
        return $result;
    }

    /**
     * Returns the key of the entity.
     * @param GenericEntity $entity
     * @return string
     */
    protected function getEntityKey(GenericEntity $entity): string
    {
        return "{$entity->getType()}|{$entity->getName()}";
    }

    /**
     * Creates the final response of the request.
     * @param array|GenericEntity[] $entities
     * @return GenericDetailsResponse
     */
    protected function createResponse(array $entities): GenericDetailsResponse
    {
        $result = new GenericDetailsResponse();
        $result->setEntities($entities);
        return $result;
    }
}
