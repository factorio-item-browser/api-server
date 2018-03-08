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
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
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
        $items = $this->itemService->getRandom($requestData->getInteger('numberOfResults'));
        $recipes = $this->fetchRecipes(array_keys($items));

        $clientItems = [];
        foreach ($items as $item) {
            $clientItem = new GenericEntityWithRecipes();
            $clientItem
                ->setType($item->getType())
                ->setName($item->getName());

            foreach ($recipes[$item->getId()] ?? [] as $recipe) {
                $clientItem->addRecipe(RecipeMapper::mapDatabaseRecipeToClientRecipe(
                    $recipe,
                    $this->translationService
                ));
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
     * Fetches the recipes to the specified item IDs.
     * @param array|int[] $itemIds
     * @return array|Recipe[][]
     */
    protected function fetchRecipes(array $itemIds): array
    {
        $groupedRecipeIds = $this->recipeService->getIdsWithProducts($itemIds);

        $recipeIds = iterator_to_array(
            new RecursiveIteratorIterator(new RecursiveArrayIterator($groupedRecipeIds)),
            false
        );
        $recipes = $this->recipeService->getDetailsByIds($recipeIds);

        $result = [];
        foreach ($groupedRecipeIds as $itemId => $itemRecipeIds) {
            foreach ($itemRecipeIds as $recipeName => $recipeIds) {
                foreach ($recipeIds as $recipeId) {
                    if (isset($recipes[$recipeId])) {
                        $result[$itemId][] = $recipes[$recipeId];
                    }
                }
            }
        }
        return $result;
    }
}