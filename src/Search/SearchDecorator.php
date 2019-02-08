<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Search\Result\AbstractResult;
use FactorioItemBrowser\Api\Server\Search\Result\ItemResult;
use FactorioItemBrowser\Api\Server\Search\Result\RecipeResult;

/**
 * The class decorating the search results to the client entities,
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchDecorator
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
     * Decorates the search results to client entities.
     * @param array|AbstractResult[] $searchResults
     * @param int $numberOfRecipesPerResult
     * @return array|GenericEntityWithRecipes[]
     * @throws MapperException
     */
    public function decorate(array $searchResults, int $numberOfRecipesPerResult): array
    {
        $itemIds = [];
        $groupedRecipeIds = [];

        foreach ($searchResults as $searchResult) {
            $groupedRecipeIds = array_merge(
                $groupedRecipeIds,
                array_slice($searchResult->getGroupedRecipeIds(), 0, $numberOfRecipesPerResult)
            );
            if ($searchResult instanceof ItemResult) {
                $itemIds[] = $searchResult->getId();
            }
        }

        if (count($groupedRecipeIds) > 0) {
            $allRecipeIds = call_user_func_array('array_merge', array_values($groupedRecipeIds));
        } else {
            $allRecipeIds = [];
        }

        $items = $this->itemService->getByIds($itemIds);
        $recipes = $this->recipeService->getDetailsByIds($allRecipeIds);

        $entities = [];
        foreach ($searchResults as $searchResult) {
            $entity = new GenericEntityWithRecipes();
            if ($searchResult instanceof ItemResult && isset($items[$searchResult->getId()])) {
                $item = $items[$searchResult->getId()];
                $entity->setType($item->getType())
                       ->setName($item->getName());
            } elseif ($searchResult instanceof RecipeResult && isset($recipes[$searchResult->getFirstRecipeId()])) {
                 $entity->setType(EntityType::RECIPE)
                        ->setName($recipes[$searchResult->getFirstRecipeId()]->getName());
            }

            foreach (array_slice($searchResult->getGroupedRecipeIds(), 0, $numberOfRecipesPerResult) as $recipeIds) {
                $currentRecipe = null;
                foreach ($recipeIds as $recipeId) {
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
                    $entity->addRecipe($currentRecipe);
                }
            }
            $entity->setTotalNumberOfRecipes(count($searchResult->getGroupedRecipeIds()));

            $this->translationService->addEntityToTranslate($entity);
            $entities[] = $entity;
        }
        return $entities;
    }
}
