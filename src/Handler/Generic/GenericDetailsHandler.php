<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\MachineService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;

/**
 * The handler of the /generic/details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericDetailsHandler extends AbstractGenericHandler
{
    /**
     * The database item service.
     * @var ItemService
     */
    protected $itemService;

    /**
     * The database service of the machines.
     * @var MachineService
     */
    protected $machineService;

    /**
     * The database recipe service.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the request handler.
     * @param ItemService $itemService
     * @param MachineService $machineService
     * @param RecipeService $recipeService
     * @param TranslationService $translationService
     */
    public function __construct(
        ItemService $itemService,
        MachineService $machineService,
        RecipeService $recipeService,
        TranslationService $translationService
    ) {
        $this->itemService = $itemService;
        $this->machineService = $machineService;
        $this->recipeService = $recipeService;
        $this->translationService = $translationService;
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $namesByTypes = $this->getEntityNamesByType($requestData);
        $entities = [];

        $recipeNames = $namesByTypes[EntityType::RECIPE] ?? [];
        foreach ($this->recipeService->filterAvailableNames($recipeNames) as $recipeName) {
            $entities[] = $this->createGenericEntity(EntityType::RECIPE, $recipeName);
        }
        unset($namesByTypes[EntityType::RECIPE]);

        $machineNames = $namesByTypes[EntityType::MACHINE] ?? [];
        foreach ($this->machineService->filterAvailableNames($machineNames) as $machineName) {
            $entities[] = $this->createGenericEntity(EntityType::MACHINE, $machineName);
        }
        unset($namesByTypes[EntityType::MACHINE]);

        foreach ($this->itemService->filterAvailableTypesAndNames($namesByTypes) as $type => $itemNames) {
            foreach ($itemNames as $itemName) {
                $entities[] = $this->createGenericEntity($type, $itemName);
            }
        }

        $this->translationService->translateEntities();
        return [
            'entities' => $entities
        ];
    }

    /**
     * Creates a generic entity with the specified type and name.
     * @param string $type
     * @param string $name
     * @return GenericEntity
     */
    protected function createGenericEntity(string $type, string $name): GenericEntity
    {
        $entity = new GenericEntity();
        $entity->setType($type)
            ->setName($name);

        $this->translationService->addEntityToTranslate($entity);
        return $entity;
    }
}