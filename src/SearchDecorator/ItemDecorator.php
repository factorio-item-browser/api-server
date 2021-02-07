<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Search\Entity\Result\ItemResult;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * The decorator of the item search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @implements SearchDecoratorInterface<ItemResult>
 */
class ItemDecorator implements SearchDecoratorInterface
{
    protected ItemRepository $itemRepository;
    protected MapperManagerInterface $mapperManager;
    protected RecipeDecorator $recipeDecorator;

    protected int $numberOfRecipesPerResult = 0;
    /** @var array<string, UuidInterface> */
    protected array $announcedItemIds = [];
    /** @var array<string, DatabaseItem> */
    protected array $databaseItems = [];

    public function __construct(
        ItemRepository $itemRepository,
        MapperManagerInterface $mapperManager,
        RecipeDecorator $recipeDecorator
    ) {
        $this->itemRepository = $itemRepository;
        $this->mapperManager = $mapperManager;
        $this->recipeDecorator = $recipeDecorator;
    }

    public function getSupportedResultClass(): string
    {
        return ItemResult::class;
    }

    public function initialize(int $numberOfRecipesPerResult): void
    {
        $this->numberOfRecipesPerResult = $numberOfRecipesPerResult;
        $this->announcedItemIds = [];
        $this->databaseItems = [];
    }

    /**
     * @param ItemResult $searchResult
     */
    public function announce(ResultInterface $searchResult): void
    {
        if ($searchResult->getId() !== null) {
            $this->announcedItemIds[$searchResult->getId()->toString()] = $searchResult->getId();
        }
        foreach (array_slice($searchResult->getRecipes(), 0, $this->numberOfRecipesPerResult) as $recipeResult) {
            $this->recipeDecorator->announce($recipeResult);
        }
    }

    public function prepare(): void
    {
        $this->databaseItems = [];
        foreach ($this->itemRepository->findByIds(array_values($this->announcedItemIds)) as $item) {
            $this->databaseItems[$item->getId()->toString()] = $item;
        }
    }

    /**
     * @param ItemResult $searchResult
     * @return GenericEntityWithRecipes|null
     */
    public function decorate(ResultInterface $searchResult): ?GenericEntityWithRecipes
    {
        $entity = $this->mapItemWithId($searchResult->getId());
        if ($entity === null) {
            return null;
        }

        foreach (array_slice($searchResult->getRecipes(), 0, $this->numberOfRecipesPerResult) as $recipeResult) {
            $recipe = $this->recipeDecorator->decorateRecipe($recipeResult);
            if ($recipe instanceof ClientRecipe) {
                $entity->recipes[] = $recipe;
            }
        }
        $entity->totalNumberOfRecipes = count($searchResult->getRecipes());
        return $entity;
    }

    /**
     * Maps the item with the specified id.
     * @param UuidInterface|null $itemId
     * @return GenericEntityWithRecipes|null
     */
    protected function mapItemWithId(?UuidInterface $itemId): ?GenericEntityWithRecipes
    {
        if ($itemId === null) {
            return null;
        }
        $itemId = $itemId->toString();
        if (!isset($this->databaseItems[$itemId])) {
            return null;
        }

        return $this->mapperManager->map($this->databaseItems[$itemId], new GenericEntityWithRecipes());
    }
}
