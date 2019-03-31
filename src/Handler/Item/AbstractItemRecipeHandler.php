<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Database\Entity\Item as DatabaseItem;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Exception\EntityNotFoundException;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;

/**
 * The abstract class of the item recipe request handlers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractItemRecipeHandler extends AbstractRequestHandler
{
    /**
     * The item repository.
     * @var ItemRepository
     */
    protected $itemRepository;

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
     * Initializes the request handler.
     * @param ItemRepository $itemRepository
     * @param MapperManagerInterface $mapperManager
     * @param RecipeService $recipeService
     */
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
     * Fetches the item from the database.
     * @param string $type
     * @param string $name
     * @param AuthorizationToken $authorizationToken
     * @return DatabaseItem
     * @throws EntityNotFoundException
     */
    protected function fetchItem(string $type, string $name, AuthorizationToken $authorizationToken): DatabaseItem
    {
        $items = $this->itemRepository->findByTypesAndNames(
            [$type => [$name]],
            $authorizationToken->getEnabledModCombinationIds()
        );
        $item = reset($items);

        if (!$item instanceof DatabaseItem) {
            throw new EntityNotFoundException($type, $name);
        }
        return $item;
    }

    /**
     * Creates the item entity to use in the response.
     * @param DatabaseItem $item
     * @param RecipeDataCollection $recipeData
     * @param int $totalNumberOfRecipes
     * @return GenericEntityWithRecipes
     * @throws MapperException
     */
    protected function createResponseEntity(
        DatabaseItem $item,
        RecipeDataCollection $recipeData,
        int $totalNumberOfRecipes
    ): GenericEntityWithRecipes {
        $result = new GenericEntityWithRecipes();

        $this->mapperManager->map($item, $result);
        $this->mapperManager->map($recipeData, $result);
        $result->setTotalNumberOfRecipes($totalNumberOfRecipes);

        return $result;
    }
}
