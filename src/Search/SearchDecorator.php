<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Search;

use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
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
     * Decorates the search results to client entities.
     * @param array|AbstractResult[] $searchResults
     * @return array|GenericEntityWithRecipes[]
     */
    public function decorate(array $searchResults): array
    {
        $itemIds = [];
        $recipeIds = [];

        foreach ($searchResults as $searchResult) {
            $recipeIds = array_merge($recipeIds, $searchResult->getRecipeIds());
            if ($searchResult instanceof ItemResult) {
                $itemIds[] = $searchResult->getId();
            }
        }

        $items = $this->itemService->getByIds($itemIds);
        $recipes = $this->recipeService->getDetailsByIds($recipeIds);

        $entities = [];
        foreach ($searchResults as $searchResult) {
            $entity = new GenericEntityWithRecipes();
            if ($searchResult instanceof ItemResult && isset($items[$searchResult->getId()])) {
                $item = $items[$searchResult->getId()];
                $entity
                    ->setType($item->getType())
                    ->setName($item->getName());
            } elseif ($searchResult instanceof RecipeResult && isset($recipes[$searchResult->getId()])) {
                 $entity
                     ->setType(EntityType::RECIPE)
                     ->setName($recipes[$searchResult->getId()]->getName());
            }

            foreach ($searchResult->getRecipeIds() as $recipeId) {
                if (isset($recipes[$recipeId])) {
                    $entity->addRecipe(RecipeMapper::mapDatabaseRecipeToClientRecipe(
                        $recipes[$recipeId],
                        $this->translationService
                    ));
                }
            }

            $this->translationService->addEntityToTranslate($entity);
            $entities[] = $entity;
        }
        return $entities;
    }
}