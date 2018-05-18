<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Entity\Machine;
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
     * @param MachineService $machineService
     * @param RecipeService $recipeService
     * @param TranslationService $translationService
     */
    public function __construct(
        MachineService $machineService,
        RecipeService $recipeService,
        TranslationService $translationService
    )
    {
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

        $craftingCategory = reset($recipes)->getCraftingCategory();
        $databaseMachines = $this->machineService->getByCraftingCategory($craftingCategory);

        $slicedDatabaseMachines = array_slice(
            $this->sortMachines($databaseMachines),
            $requestData->getInteger('indexOfFirstResult'),
            $requestData->getInteger('numberOfResults')
        );
        $clientMachines = [];
        foreach ($slicedDatabaseMachines as $databaseMachine) {
            $clientMachines[] = MachineMapper::mapDatabaseMachineToClientMachine(
                $databaseMachine,
                $this->translationService
            );
        }

        $this->translationService->translateEntities();
        return [
            'machines' => $clientMachines,
            'totalNumberOfResults' => count($databaseMachines)
        ];
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