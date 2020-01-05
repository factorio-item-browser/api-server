<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use Doctrine\Common\Collections\Collection;
use FactorioItemBrowser\Api\Database\Entity\CraftingCategory;
use FactorioItemBrowser\Api\Database\Entity\Machine;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Database\Entity\RecipeIngredient;
use FactorioItemBrowser\Api\Database\Entity\RecipeProduct;
use FactorioItemBrowser\Api\Database\Repository\MachineRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Common\Constant\Constant;
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
     * The machine names to prefer.
     */
    protected const PREFERRED_MACHINE_NAME = Constant::ENTITY_NAME_CHARACTER;

    /**
     * The repository of the machines.
     * @var MachineRepository
     */
    protected $machineRepository;

    /**
     * MachineService constructor.
     * @param MachineRepository $machineRepository
     */
    public function __construct(MachineRepository $machineRepository)
    {
        $this->machineRepository = $machineRepository;
    }

    /**
     * Returns the machines supporting the specified crafting category.
     * @param CraftingCategory $craftingCategory
     * @param AuthorizationToken $authorizationToken
     * @return array|Machine[]
     */
    public function getMachinesByCraftingCategory(
        CraftingCategory $craftingCategory,
        AuthorizationToken $authorizationToken
    ): array {
        return $this->machineRepository->findByCraftingCategoryName(
            $authorizationToken->getCombinationId(),
            $craftingCategory->getName()
        );
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
            if ($this->isMachineValid($machine, $numberOfItems, $numberOfFluidInputs, $numberOfFluidOutputs)) {
                $result[] = $machine;
            }
        }
        return $result;
    }

    /**
     * Counts the item with a type.
     * @param Collection<int,RecipeIngredient|RecipeProduct> $entities
     * @param string $type
     * @return int
     */
    protected function countItemType(Collection $entities, string $type): int
    {
        $result = 0;
        foreach ($entities as $entity) {
            if ($this->getItemType($entity) === $type) {
                ++$result;
            }
        }
        return $result;
    }

    /**
     * Returns the item type of the entity.
     * @param object $entity
     * @return string|null
     */
    protected function getItemType($entity): ?string
    {
        $result = null;
        if (($entity instanceof RecipeIngredient || $entity instanceof RecipeProduct)) {
            $result = $entity->getItem()->getType();
        }
        return $result;
    }

    /**
     * Returns whether the machine is valid with the number of inputs and outputs.
     * @param Machine $machine
     * @param int $numberOfItems
     * @param int $numberOfFluidInputs
     * @param int $numberOfFluidOutputs
     * @return bool
     */
    protected function isMachineValid(
        Machine $machine,
        int $numberOfItems,
        int $numberOfFluidInputs,
        int $numberOfFluidOutputs
    ): bool {
        return ($machine->getNumberOfItemSlots() === Machine::VALUE_UNLIMITED_SLOTS
                || $machine->getNumberOfItemSlots() >= $numberOfItems)
            && $machine->getNumberOfFluidInputSlots() >= $numberOfFluidInputs
            && $machine->getNumberOfFluidOutputSlots() >= $numberOfFluidOutputs;
    }

    /**
     * Sorts the machines, preferring the player in the front.
     * @param array|Machine[] $machines
     * @return array|Machine[]
     */
    public function sortMachines(array $machines): array
    {
        usort($machines, [$this, 'compareMachines']);
        return array_values($machines);
    }

    /**
     * Compares the two machines for sorting.
     * @param Machine $left
     * @param Machine $right
     * @return int
     */
    protected function compareMachines(Machine $left, Machine $right): int
    {
        if ($left->getName() === self::PREFERRED_MACHINE_NAME) {
            $result = -1;
        } elseif ($right->getName() === self::PREFERRED_MACHINE_NAME) {
            $result = 1;
        } else {
            $result = strtolower($left->getName()) <=> strtolower($right->getName());
        }
        return $result;
    }
}
