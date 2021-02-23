<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use Ramsey\Uuid\UuidInterface;

/**
 * The abstract handler for the item requests.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractItemHandler
{
    protected ItemRepository $itemRepository;
    protected MapperManagerInterface $mapperManager;
    protected RecipeService $recipeService;

    public function __construct(
        ItemRepository $itemRepository,
        MapperManagerInterface $mapperManager,
        RecipeService $recipeService
    ) {
        $this->itemRepository = $itemRepository;
        $this->mapperManager = $mapperManager;
        $this->recipeService = $recipeService;
    }

    /**
     * Fetches the specified item from the database, throwing an exception when it is not found.
     * @param UuidInterface $combinationId
     * @param string $type
     * @param string $name
     * @return Item
     * @throws EntityNotFoundException
     */
    protected function fetchItem(UuidInterface $combinationId, string $type, string $name): Item
    {
        $namesByTypes = new NamesByTypes();
        $namesByTypes->addName($type, $name);

        $items = $this->itemRepository->findByTypesAndNames($combinationId, $namesByTypes);
        $item = reset($items);
        if (!$item instanceof Item) {
            throw new EntityNotFoundException($type, $name);
        }
        return $item;
    }

    /**
     * Maps the specified items, fetching the production recipes for them.
     * @param UuidInterface $combinationId
     * @param array<Item> $items
     * @param int $numberOfRecipesPerResult
     * @return array<GenericEntityWithRecipes>
     */
    protected function mapItems(UuidInterface $combinationId, array $items, int $numberOfRecipesPerResult): array
    {
        if ($numberOfRecipesPerResult === 0) {
            // We are not interested in any recipe data, so skip fetching and mapping for it.
            // @phpstan-ignore-next-line Keys "recipes" and "totalNumberOfRecipes" intentionally cut from the response.
            return array_map(
                fn (Item $item): GenericEntity => $this->mapperManager->map($item, new GenericEntity()),
                $items,
            );
        }

        // Prefetch recipes for later mapping
        $recipeData = $this->recipeService->getDataWithProducts($combinationId, $items);
        $this->recipeService->getDetailsByIds($recipeData->getAllIds());

        return array_map(
            function (Item $item) use ($recipeData, $numberOfRecipesPerResult): GenericEntityWithRecipes {
                return $this->createItem(
                    $item,
                    $recipeData->filterItemId($item->getId()),
                    $numberOfRecipesPerResult,
                    0
                );
            },
            $items,
        );
    }

    /**
     * Creates a mapped item object for the response.
     * @param Item $item
     * @param RecipeDataCollection $recipeData
     * @param int $numberOfRecipes
     * @param int $indexOfFirstRecipe
     * @return GenericEntityWithRecipes
     */
    protected function createItem(
        Item $item,
        RecipeDataCollection $recipeData,
        int $numberOfRecipes,
        int $indexOfFirstRecipe
    ): GenericEntityWithRecipes {
        $entity = new GenericEntityWithRecipes();

        $this->mapperManager->map($item, $entity);
        $this->mapperManager->map($recipeData->limitNames($numberOfRecipes, $indexOfFirstRecipe), $entity);
        $entity->totalNumberOfRecipes = $recipeData->countNames();

        return $entity;
    }
}
