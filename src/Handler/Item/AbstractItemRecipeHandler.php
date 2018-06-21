<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Server\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Exception\ApiServerException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Mapper\ItemMapper;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The abstract class of the item recipe request handlers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractItemRecipeHandler extends AbstractRequestHandler
{
    /**
     * The item mapper.
     * @var ItemMapper
     */
    protected $itemMapper;

    /**
     * The database item service.
     * @var ItemService
     */
    protected $itemService;

    /**
     * The recipe mapper.
     * @var RecipeMapper
     */
    protected $recipeMapper;

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
     * @param ItemMapper $itemMapper
     * @param ItemService $itemService
     * @param RecipeMapper $recipeMapper
     * @param RecipeService $recipeService
     * @param TranslationService $translationService
     */
    public function __construct(
        ItemMapper $itemMapper,
        ItemService $itemService,
        RecipeMapper $recipeMapper,
        RecipeService $recipeService,
        TranslationService $translationService
    ) {
        $this->itemMapper = $itemMapper;
        $this->itemService = $itemService;
        $this->recipeMapper = $recipeMapper;
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
                'name' => 'type',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ])
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
        $databaseItem = $this->itemService->getByTypeAndName(
            $requestData->getString('type'),
            $requestData->getString('name')
        );
        if (!$databaseItem instanceof DatabaseItem) {
            throw new ApiServerException('Item not found or not available in the enabled mods.', 404);
        }
        $clientItem = new GenericEntityWithRecipes();
        $this->itemMapper->mapItem($databaseItem, $clientItem);

        $groupedRecipeIds = $this->fetchGroupedRecipeIds($databaseItem);
        $totalNumberOfRecipes = count($groupedRecipeIds);
        $recipeIds = $this->limitGroupedRecipeIds(
            $groupedRecipeIds,
            max($requestData->getInteger('numberOfResults'), 0),
            max($requestData->getInteger('indexOfFirstResult'), 0)
        );

        $clientRecipes = [];
        foreach ($this->recipeService->getDetailsByIds($recipeIds) as $databaseRecipe) {
            $mappedRecipe = new RecipeWithExpensiveVersion();
            $this->recipeMapper->mapRecipe($databaseRecipe, $mappedRecipe);

            if (isset($clientRecipes[$databaseRecipe->getName()])) {
                $this->recipeMapper->combineRecipes($clientRecipes[$databaseRecipe->getName()], $mappedRecipe);
            } else {
                $clientRecipes[$databaseRecipe->getName()] = $mappedRecipe;
            }
        }

        $clientItem->setRecipes($clientRecipes)
                   ->setTotalNumberOfRecipes($totalNumberOfRecipes);

        $this->translationService->translateEntities();
        return [
            'item' => $clientItem
        ];
    }

    /**
     * Fetches the IDs of the grouped recipes.
     * @param DatabaseItem $item
     * @return array|int[][]
     */
    abstract protected function fetchGroupedRecipeIds(DatabaseItem $item): array;

    /**
     * Limits the grouped recipe ids to the specified slice.
     * @param array|int[][] $groupedRecipeIds
     * @param int $limit
     * @param int $offset
     * @return array|int[]
     */
    protected function limitGroupedRecipeIds(array $groupedRecipeIds, int $limit, int $offset): array
    {
        if ($limit > 0) {
            $groupedRecipeIds = array_slice($groupedRecipeIds, $offset, $limit);
        }
        if (count($groupedRecipeIds) > 0) {
            $recipeIds = call_user_func_array('array_merge', $groupedRecipeIds);
        } else {
            $recipeIds = [];
        }
        return $recipeIds;
    }
}