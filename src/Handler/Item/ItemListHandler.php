<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Item\ItemListRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\RecipeService;

/**
 * The handler of the /item/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemListHandler extends AbstractRequestHandler
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
     * The recipe service.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * Initializes the handler.
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
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return ItemListRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param ItemListRequest $request
     * @return ClientResponseInterface
     * @throws MapperException
     */
    protected function handleRequest($request): ClientResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();
        $items = $this->itemRepository->findAll($authorizationToken->getCombinationId());
        $limitedItems = array_slice($items, $request->getIndexOfFirstResult(), $request->getNumberOfResults());

        $recipeData = $this->recipeService->getDataWithProducts($limitedItems, $authorizationToken);
        // Prefetch all recipes for later mapping
        $this->recipeService->getDetailsByIds($recipeData->getAllIds());

        $mappedItems = $this->mapItems($limitedItems, $recipeData, $request->getNumberOfRecipesPerResult());
        return $this->createResponse($mappedItems, count($items));
    }

    /**
     * Maps the recipes to the response entities.
     * @param array|Item[] $items
     * @param RecipeDataCollection $recipeData
     * @param int $numberOfRecipes
     * @return array|GenericEntityWithRecipes[]
     * @throws MapperException
     */
    protected function mapItems(array $items, RecipeDataCollection $recipeData, int $numberOfRecipes): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->createItem($item, $recipeData->filterItemId($item->getId()), $numberOfRecipes);
        }
        return $result;
    }

    /**
     * Creates the response entity for the item.
     * @param Item $item
     * @param RecipeDataCollection $recipeData
     * @param int $numberOfRecipes
     * @return GenericEntityWithRecipes
     * @throws MapperException
     */
    protected function createItem(
        Item $item,
        RecipeDataCollection $recipeData,
        int $numberOfRecipes
    ): GenericEntityWithRecipes {
        $result = new GenericEntityWithRecipes();

        $this->mapperManager->map($item, $result);
        $this->mapperManager->map($recipeData->limitNames($numberOfRecipes, 0), $result);
        $result->setTotalNumberOfRecipes($recipeData->countNames());

        return $result;
    }

    /**
     * Creates the response to send to the client.
     * @param array|GenericEntityWithRecipes[] $items
     * @param int $numberOfItems
     * @return ItemListResponse
     */
    protected function createResponse(array $items, int $numberOfItems): ItemListResponse
    {
        $result = new ItemListResponse();
        $result->setItems($items)
               ->setTotalNumberOfResults($numberOfItems);
        return $result;
    }
}
