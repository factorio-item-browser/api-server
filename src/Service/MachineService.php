<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use Doctrine\Common\Collections\Collection;
use FactorioItemBrowser\Api\Database\Data\MachineData;
use FactorioItemBrowser\Api\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Entity\RecipeIngredient;
use FactorioItemBrowser\Api\Database\Entity\RecipeProduct;
use FactorioItemBrowser\Api\Database\Filter\DataFilter;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Common\Constant\ItemType;

/**
 * The service class handling machines.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MachineService
{
    /**
     * The data filter.
     * @var DataFilter
     */
    protected $dataFilter;

    /**
     * The repository of the machines.
     * @var MachineRepository
     */
    protected $machineRepository;

    /**
     * MachineService constructor.
     * @param DataFilter $dataFilter
     * @param MachineRepository $machineRepository
     */
    public function __construct(DataFilter $dataFilter, MachineRepository $machineRepository)
    {
        $this->dataFilter = $dataFilter;
        $this->machineRepository = $machineRepository;
    }

    /**
     * Returns the machines supporting the specified crafting category.
     * @param CraftingCategory $craftingCategory
     * @param AuthorizationToken $authorizationToken
     * @return array|Machine[]
     */
    public function getByCraftingCategory(
        CraftingCategory $craftingCategory,
        AuthorizationToken $authorizationToken
    ): array {
        $allMachineData = $this->machineRepository->findDataByCraftingCategories(
            [$craftingCategory->getName()],
            $authorizationToken->getEnabledModCombinationIds()
        );
        $machineIds = $this->extractIdsFromMachineData($allMachineData);
        return $this->machineRepository->findByIds($machineIds);
    }

    /**
     * Extracts the machine ids of the machine data array.
     * @param array|MachineData[] $allMachineData
     * @return array|int[]
     */
    protected function extractIdsFromMachineData(array $allMachineData): array
    {
        $result = [];
        foreach ($this->dataFilter->filter($allMachineData) as $machineData) {
            if ($machineData instanceof MachineData) {
                $result[] = $machineData->getId();
            }
        }
        return $result;
    }

    /**
     * Filters the machines which actually can craft the recipe.
     * @param array|Machine[] $machines
     * @param Recipe $recipe
     * @return array|Machine[]
     */
    public function filterMachinesForRecipe(array $machines, Recipe $recipe): array
    {
        $numberOfItems = $this->countItemType($recipe->getIngredients(), ItemType::ITEM);
        $numberOfFluidInputs = $this->countItemType($recipe->getIngredients(), ItemType::FLUID);
        $numberOfFluidOutputs = $this->countItemType($recipe->getProducts(), ItemType::FLUID);

        $result = [];
        foreach ($machines as $machine) {
            if (($machine->getNumberOfItemSlots() === Machine::VALUE_UNLIMITED_SLOTS
                    || $machine->getNumberOfItemSlots() >= $numberOfItems)
                && $machine->getNumberOfFluidInputSlots() >= $numberOfFluidInputs
                && $machine->getNumberOfFluidOutputSlots() >= $numberOfFluidOutputs
            ) {
                $result[] = $machine;
            }
        }
        return $result;
    }

    /**
     * Counts the item with a type.
     * @param Collection|RecipeIngredient[]|RecipeProduct[] $entities
     * @param string $type
     * @return int
     */
    protected function countItemType(Collection $entities, string $type): int
    {
        $result = 0;
        foreach ($entities as $entity) {
            if (($entity instanceof RecipeIngredient || $entity instanceof RecipeProduct)
                && $entity->getItem()->getType() === $type
            ) {
                ++$result;
            }
        }
        return $result;
    }

    /**
     * Sorts the machines, preferring the player in the front.
     * @param array|Machine[] $machines
     * @return array|Machine[]
     */
    public function sortMachines(array $machines): array
    {
        usort($machines, function (Machine $left, Machine $right): int {
            if ($left->getName() === 'player') {
                $result = -1;
            } elseif ($right->getName() === 'player') {
                $result = 1;
            } else {
                $result = strtolower($left->getName()) <=> strtolower($right->getName());
            }
            return $result;
        });
        return array_values($machines);
    }
}
