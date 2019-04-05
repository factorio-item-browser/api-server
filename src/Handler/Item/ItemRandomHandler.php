<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use BluePsyduck\MapperManager\Exception\MapperException;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Item\ItemRandomRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemRandomResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Database\Entity\Item;
use FactorioItemBrowser\Api\Database\Repository\ItemRepository;
use FactorioItemBrowser\Api\Server\Collection\RecipeDataCollection;
use FactorioItemBrowser\Api\Server\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;

/**
 * The handler of the /item/random request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemRandomHandler extends AbstractRequestHandler
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
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return ItemRandomRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param ItemRandomRequest $request
     * @return ResponseInterface
     * @throws MapperException
     */
    protected function handleRequest($request): ResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();

        $items = $this->itemRepository->findRandom(
            $request->getNumberOfResults(),
            $authorizationToken->getEnabledModCombinationIds()
        );
        $recipeData = $this->recipeService->getDataWithProducts($items, $authorizationToken);

        // Prefetch all recipes for later mapping
        $this->recipeService->getDetailsByIds($recipeData->getAllIds());

        return $this->createResponse($items, $recipeData, $request->getNumberOfRecipesPerResult());
    }

    /**
     * Creates the response to send to the client.
     * @param array|Item[] $items
     * @param RecipeDataCollection $recipeData
     * @param int $numberOfRecipesPerResult
     * @return ItemRandomResponse
     * @throws MapperException
     */
    protected function createResponse(
        array $items,
        RecipeDataCollection $recipeData,
        int $numberOfRecipesPerResult
    ): ItemRandomResponse {
        $result = new ItemRandomResponse();

        foreach ($items as $item) {
            $result->addItem($this->createItem(
                $item,
                $recipeData->filterItemId($item->getId()),
                $numberOfRecipesPerResult
            ));
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
}
