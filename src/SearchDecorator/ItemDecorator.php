<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Transfer\Recipe as ClientRecipe;
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
 * @extends AbstractEntityDecorator<ItemResult>
 */
class ItemDecorator extends AbstractEntityDecorator
{
    protected ItemRepository $itemRepository;
    protected RecipeDecorator $recipeDecorator;

    public function __construct(
        ItemRepository $itemRepository,
        MapperManagerInterface $mapperManager,
        RecipeDecorator $recipeDecorator
    ) {
        parent::__construct($mapperManager);

        $this->itemRepository = $itemRepository;
        $this->recipeDecorator = $recipeDecorator;
    }

    public function getSupportedResultClass(): string
    {
        return ItemResult::class;
    }

    /**
     * @param ItemResult $searchResult
     */
    public function announce(ResultInterface $searchResult): void
    {
        $this->addAnnouncedId($searchResult->getId());
        foreach (array_slice($searchResult->getRecipes(), 0, $this->numberOfRecipesPerResult) as $recipeResult) {
            $this->recipeDecorator->announce($recipeResult);
        }
    }

    protected function fetchDatabaseEntities(array $ids): array
    {
        $items = [];
        foreach ($this->itemRepository->findByIds($ids) as $item) {
            $items[$item->getId()->toString()] = $item;
        }
        return $items;
    }

    /**
     * @param ItemResult $searchResult
     * @return UuidInterface|null
     */
    protected function getIdFromResult(ResultInterface $searchResult): ?UuidInterface
    {
        return $searchResult->getId();
    }

    /**
     * @param ItemResult $searchResult
     * @param GenericEntityWithRecipes $entity
     */
    protected function hydrateRecipes(ResultInterface $searchResult, GenericEntityWithRecipes $entity): void
    {
        foreach (array_slice($searchResult->getRecipes(), 0, $this->numberOfRecipesPerResult) as $recipeResult) {
            $recipe = $this->recipeDecorator->decorateRecipe($recipeResult);
            if ($recipe instanceof ClientRecipe) {
                $entity->recipes[] = $recipe;
            }
        }
        $entity->totalNumberOfRecipes = count($searchResult->getRecipes());
    }
}
