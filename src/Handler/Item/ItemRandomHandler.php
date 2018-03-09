<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
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
     * @param RecipeService $recipeService
     * @param TranslationService $translationService
     */
    public function __construct(
        ItemService $itemService,
        RecipeService $recipeService,
        TranslationService $translationService
    ) {
        $this->itemService = $itemService;
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
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $numberOfRecipesPerResult = $requestData->getInteger('numberOfRecipesPerResult');

        $items = $this->itemService->getRandom($requestData->getInteger('numberOfResults'));
        $recipeIdsByItems = $this->fetchRecipeIds(array_keys($items));
        $recipes = $this->fetchRecipeDetails($recipeIdsByItems, $numberOfRecipesPerResult);

        $clientItems = [];
        foreach ($items as $item) {
            $clientItem = new GenericEntityWithRecipes();
            $clientItem
                ->setType($item->getType())
                ->setName($item->getName());

            if (isset($recipeIdsByItems[$item->getId()])) {
                foreach (array_slice($recipeIdsByItems[$item->getId()], 0, $numberOfRecipesPerResult) as $recipeId) {
                    if (isset($recipes[$recipeId])) {
                        $clientItem->addRecipe(RecipeMapper::mapDatabaseRecipeToClientRecipe(
                            $recipes[$recipeId],
                            $this->translationService
                        ));
                    }
                }
                $clientItem->setTotalNumberOfRecipes(count($recipeIdsByItems[$item->getId()]));
            }

            $this->translationService->addEntityToTranslate($clientItem);
            $clientItems[] = $clientItem;
        }

        $this->translationService->translateEntities();
        return [
            'items' => $clientItems
        ];
    }


    /**
     * Fetches the recipe ids of the specified items.
     * @param array|int[] $itemIds
     * @return array|int[][]
     */
    protected function fetchRecipeIds(array $itemIds): array
    {
        $result = [];
        $groupedRecipeIds = $this->recipeService->getIdsWithProducts($itemIds);
        foreach ($groupedRecipeIds as $itemId => $itemRecipeIds) {
            foreach ($itemRecipeIds as $recipeIds) {
                $result[$itemId] = array_merge(
                    $result[$itemId] ?? [],
                    $recipeIds
                );
            }
        }
        return $result;
    }

    /**
     * Fetches the recipe details of the specified recipe ids.
     * @param array|int[][] $recipeIdsByItems
     * @param int $numberOfRecipesPerResult
     * @return array|Recipe[]
     */
    protected function fetchRecipeDetails(array $recipeIdsByItems, int $numberOfRecipesPerResult): array
    {
        $allRecipeIds = [];
        foreach ($recipeIdsByItems as $itemId => $recipeIds) {
            $allRecipeIds = array_merge(
                $allRecipeIds,
                array_slice($recipeIds, 0, $numberOfRecipesPerResult)
            );
        }
        return $this->recipeService->getDetailsByIds($allRecipeIds);
    }
}