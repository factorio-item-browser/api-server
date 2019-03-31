<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericDetailsResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Database\Repository\RecipeRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Traits\TypeAndNameFromEntityExtractorTrait;
use FactorioItemBrowser\Common\Constant\EntityType;
use FactorioItemBrowser\Common\Constant\ItemType;

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
     * The map of the entity types to their processing methods.
     */
    protected const MAP_TYPE_TO_METHOD = [
        EntityType::ITEM => 'processItems',
        EntityType::FLUID => 'processFluids',
        EntityType::MACHINE => 'processMachines',
        EntityType::RECIPE => 'processRecipes',
    ];

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
     */
    protected function process(NamesByTypes $namesByTypes, AuthorizationToken $authorizationToken): array
    {
        $result = [];
        foreach (self::MAP_TYPE_TO_METHOD as $type => $method) {
            $result = array_merge(
                $result,
                $this->$method($namesByTypes->getNames($type), $authorizationToken)
            );
        }
        return array_values($result);
    }

    /**
     * Processes the items.
     * @param array|string $names
     * @param AuthorizationToken $authorizationToken
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function processItems(array $names, AuthorizationToken $authorizationToken): array
    {
        $items = $this->itemRepository->findByTypesAndNames(
            [ItemType::ITEM => $names],
            $authorizationToken->getEnabledModCombinationIds()
        );

        return $this->mapObjectsToEntities($items);
    }

    /**
     * Processes the fluids.
     * @param array|string $names
     * @param AuthorizationToken $authorizationToken
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function processFluids(array $names, AuthorizationToken $authorizationToken): array
    {
        $fluids = $this->itemRepository->findByTypesAndNames(
            [ItemType::FLUID => $names],
            $authorizationToken->getEnabledModCombinationIds()
        );

        return $this->mapObjectsToEntities($fluids);
    }

    /**
     * Processes the machines.
     * @param array|string $names
     * @param AuthorizationToken $authorizationToken
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function processMachines(array $names, AuthorizationToken $authorizationToken): array
    {
        $machines = $this->machineRepository->findDataByNames(
            $names,
            $authorizationToken->getEnabledModCombinationIds()
        );

        return $this->mapObjectsToEntities($machines);
    }
    
    /**
     * Processes the recipes.
     * @param array|string $names
     * @param AuthorizationToken $authorizationToken
     * @return array|GenericEntity[]
     * @throws MapperException
     */
    protected function processRecipes(array $names, AuthorizationToken $authorizationToken): array
    {
        $recipes = $this->recipeRepository->findDataByNames(
            $names,
            $authorizationToken->getEnabledModCombinationIds()
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
     * Returns the key to match duplicated entities.
     * @param GenericEntity $entity
     * @return string
     */
    protected function getEntityKey(GenericEntity $entity): string
    {
        return implode('|', [$entity->getType(), $entity->getName()]);
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
