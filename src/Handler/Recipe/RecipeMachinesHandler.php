<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Constant\ItemType;
use FactorioItemBrowser\Api\Client\Entity\Machine as ClientMachine;
use FactorioItemBrowser\Api\Server\Database\Entity\Machine;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Database\Service\MachineService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Mapper\MachineMapper;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The handler of the /recipe/machines request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeMachinesHandler extends AbstractRequestHandler
{
    /**
     * The machine mapper.
     * @var MachineMapper
     */
    protected $machineMapper;

    /**
     * The database service of the machines.
     * @var MachineService
     */
    protected $machineService;

    /**
     * The database service of the recipes.
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
     * @param MachineMapper $machineMapper
     * @param MachineService $machineService
     * @param RecipeService $recipeService
     * @param TranslationService $translationService
     */
    public function __construct(
        MachineMapper $machineMapper,
        MachineService $machineService,
        RecipeService $recipeService,
        TranslationService $translationService
    ) {
        $this->machineMapper = $machineMapper;
        $this->machineService = $machineService;
        $this->recipeService = $recipeService;
        $this->translationService = $translationService;
    }

    /**
     * Creates the input filter to use to verify the request.
     * @return InputFilter
     */
    protected function createInputFilter(): InputFilter
    {
        $inputFilter = new InputFilter();
        $inputFilter
            ->add([
                'name' => 'name',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'numberOfResults',
                'required' => true,
                'fallback_value' => 10,
                'filters' => [
                    new ToInt()
                ],
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'indexOfFirstResult',
                'required' => true,
                'fallback_value' => 0,
                'filters' => [
                    new ToInt()
                ],
                'validators' => [
                    new NotEmpty()
                ]
            ]);
        return $inputFilter;
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $recipeIds = $this->recipeService->getIdsByNames([$requestData->getString('name')]);
        $recipes = $this->recipeService->getDetailsByIds($recipeIds);

        if (count($recipes) === 0) {
            throw new ApiServerException('Recipe not found or not available in the enabled mods.', 404);
        }

        $recipe = reset($recipes);
        $craftingCategory = $recipe->getCraftingCategory();
        $databaseMachines = $this->machineService->getByCraftingCategory($craftingCategory);
        $filteredDatabaseMachines = $this->filterMachines($recipe, $databaseMachines);

        $slicedDatabaseMachines = array_slice(
            $this->sortMachines($filteredDatabaseMachines),
            $requestData->getInteger('indexOfFirstResult'),
            $requestData->getInteger('numberOfResults')
        );
        $clientMachines = [];
        foreach ($slicedDatabaseMachines as $databaseMachine) {
            $clientMachines[] = $this->machineMapper->mapMachine($databaseMachine, new ClientMachine());
        }

        $this->translationService->translateEntities();
        return [
            'machines' => $clientMachines,
            'totalNumberOfResults' => count($filteredDatabaseMachines)
        ];
    }

    /**
     * Filters the machines to actually support the specified recipe.
     * @param Recipe $recipe
     * @param array|Machine[] $machines
     * @return array|Machine[]
     */
    protected function filterMachines(Recipe $recipe, array $machines): array
    {
        $numberOfItems = 0;
        $numberOfFluidInputs = 0;
        $numberOfFluidOutputs = 0;

        foreach ($recipe->getIngredients() as $ingredient) {
            if ($ingredient->getItem()->getType() === ItemType::ITEM) {
                ++$numberOfItems;
            } elseif ($ingredient->getItem()->getType() === ItemType::FLUID) {
                ++$numberOfFluidInputs;
            }
        }
        foreach ($recipe->getProducts() as $product) {
            if ($product->getItem()->getType() === ItemType::FLUID) {
                ++$numberOfFluidOutputs;
            }
        }

        foreach ($machines as $key => $machine) {
            if (($machine->getNumberOfItemSlots() >= 0 && $machine->getNumberOfItemSlots() < $numberOfItems)
                || $machine->getNumberOfFluidInputSlots() < $numberOfFluidInputs
                || $machine->getNumberOfFluidOutputSlots() < $numberOfFluidOutputs
            ) {
                unset($machines[$key]);
            }
        }

        return array_values($machines);
    }

    /**
     * Sorts the machines, preferring the player to be on top.
     * @param array|Machine[] $machines
     * @return array|Machine[]
     */
    protected function sortMachines(array $machines): array
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
