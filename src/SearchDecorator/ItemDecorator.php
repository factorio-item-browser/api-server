<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Search\Entity\Result\ItemResult;
use FactorioItemBrowser\Api\Search\Entity\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;

/**
 * The decorator of the item search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemDecorator implements SearchDecoratorInterface
{
    /**
     * The item service.
     * @var ItemService
     */
    protected $itemService;

    /**
     * The mapper manager.
     * @var MapperManagerInterface
     */
    protected $mapperManager;

    /**
     * The number of recipes to decorate per result.
     * @var int
     */
    protected $numberOfRecipesPerResult = 0;

    /**
     * The recipe decorator.
     * @var RecipeDecorator
     */
    protected $recipeDecorator;

    /**
     * The item ids of the announced search results.
     * @var array|int[]
     */
    protected $itemIds = [];

    /**
     * The items from the database.
     * @var array|DatabaseItem[];
     */
    protected $items = [];

    /**
     * Initializes the decorator.
     * @param ItemService $itemService
     * @param MapperManagerInterface $mapperManager
     * @param RecipeDecorator $recipeDecorator
     */
    public function __construct(
        ItemService $itemService,
        MapperManagerInterface $mapperManager,
        RecipeDecorator $recipeDecorator
    ) {
        $this->itemService = $itemService;
        $this->mapperManager = $mapperManager;
        $this->recipeDecorator = $recipeDecorator;
    }

    /**
     * Returns the result class supported by the decorator.
     * @return string
     */
    public function getSupportedResultClass(): string
    {
        return ItemResult::class;
    }

    /**
     * Initializes the decorator.
     * @param int $numberOfRecipesPerResult
     */
    public function initialize(int $numberOfRecipesPerResult): void
    {
        $this->numberOfRecipesPerResult = $numberOfRecipesPerResult;
        $this->itemIds = [];
        $this->items = [];
    }

    /**
     * Announces a search result to be decorated.
     * @param ItemResult $itemResult
     */
    public function announce($itemResult): void
    {
        $this->itemIds[] = $itemResult->getId();
        foreach ($this->getRecipesFromItem($itemResult) as $recipeResult) {
            $this->recipeDecorator->announce($recipeResult);
        }
    }

    /**
     * Prepares the data for the actual decoration.
     */
    public function prepare(): void
    {
        $itemIds = array_values(array_unique(array_filter($this->itemIds)));
        $this->items = $this->itemService->getByIds($itemIds);
    }

    /**
     * Actually decorates the search result.
     * @param ItemResult $itemResult
     * @return GenericEntityWithRecipes|null
     * @throws MapperException
     */
    public function decorate($itemResult): ?GenericEntityWithRecipes
    {
        $item = $this->items[$itemResult->getId()];
        $result = new GenericEntityWithRecipes();
        $this->mapperManager->map($item, $result);

        foreach ($this->getRecipesFromItem($itemResult) as $recipeResult) {
            $recipe = $this->recipeDecorator->decorateRecipe($recipeResult);
            if ($recipe instanceof ClientRecipe) {
                $result->addRecipe($recipe);
            }
        }
        $result->setTotalNumberOfRecipes(count($itemResult->getRecipes()));

        return $result;
    }

    /**
     * Returns the recipes from the item to process.
     * @param ItemResult $itemResult
     * @return array|RecipeResult[]
     */
    protected function getRecipesFromItem(ItemResult $itemResult): array
    {
        return array_slice($itemResult->getRecipes(), 0, $this->numberOfRecipesPerResult);
    }
}
