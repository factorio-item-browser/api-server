<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The handler of the /item/random request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemRandomHandler extends AbstractRequestHandler
{
    /**
     * The database item service.
     * @var ItemService
     */
    protected $itemService;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

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
     * @param MapperManagerInterface $mapperManager
     * @param RecipeService $recipeService
     * @param TranslationService $translationService
     */
    public function __construct(
        ItemService $itemService,
        MapperManagerInterface $mapperManager,
        RecipeService $recipeService,
        TranslationService $translationService
    ) {
        $this->itemService = $itemService;
        $this->mapperManager = $mapperManager;
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
                'name' => 'numberOfRecipesPerResult',
                'required' => true,
                'fallback_value' => 3,
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
     * @throws MapperException
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $numberOfRecipesPerResult = $requestData->getInteger('numberOfRecipesPerResult');

        $items = $this->itemService->getRandom($requestData->getInteger('numberOfResults'));
        $groupedRecipeIds = $this->recipeService->getIdsWithProducts(array_keys($items));
        $recipes = $this->fetchRecipeDetails($groupedRecipeIds, $numberOfRecipesPerResult);

        $clientItems = [];
        foreach ($items as $itemId => $item) {
            $clientItem = new GenericEntityWithRecipes();
            $this->mapperManager->map($item, $clientItem);
            $clientItem->setTotalNumberOfRecipes(count($groupedRecipeIds[$itemId] ?? []));

            foreach ($groupedRecipeIds[$itemId] ?? [] as $recipeIdGroup) {
                $currentRecipe = null;
                foreach ($recipeIdGroup as $recipeId) {
                    if (isset($recipes[$recipeId])) {
                        $mappedRecipe = new RecipeWithExpensiveVersion();
                        $this->mapperManager->map($recipes[$recipeId], $mappedRecipe);

                        if (is_null($currentRecipe)) {
                            $currentRecipe = $mappedRecipe;
                        } else {
                            $this->mapperManager->map($currentRecipe, $mappedRecipe);
                        }
                    }
                }
                if ($currentRecipe instanceof RecipeWithExpensiveVersion) {
                    $clientItem->addRecipe($currentRecipe);
                }
            }

            $clientItems[] = $clientItem;
        }

        $this->translationService->translateEntities();
        return [
            'items' => $clientItems
        ];
    }

    /**
     * Fetches the recipe details of the specified recipe ids.
     * @param array|int[][][] $groupedRecipeIds
     * @param int $numberOfRecipesPerResult
     * @return array|Recipe[]
     */
    protected function fetchRecipeDetails(array $groupedRecipeIds, int $numberOfRecipesPerResult): array
    {
        $allRecipeIds = [];
        foreach ($groupedRecipeIds as $itemId => $recipeIdsGroup) {
            foreach (array_slice($recipeIdsGroup, 0, $numberOfRecipesPerResult) as $recipeIds) {
                $allRecipeIds = array_merge(
                    $allRecipeIds,
                    $recipeIds
                );
            }
        }
        return $this->recipeService->getDetailsByIds($allRecipeIds);
    }
}
