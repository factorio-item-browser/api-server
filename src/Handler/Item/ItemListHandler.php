<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Client\Request\Item\ItemListRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler of the /item/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemListHandler extends AbstractItemHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ItemListRequest $clientRequest */
        $clientRequest = $request->getParsedBody();

        $combinationId = Uuid::fromString($clientRequest->combinationId);
        $items = $this->itemRepository->findAll($combinationId);
        $limitedItems = array_slice($items, $clientRequest->indexOfFirstResult, $clientRequest->numberOfResults);

        $response = new ItemListResponse();
        $response->items = $this->mapItems($combinationId, $limitedItems, $clientRequest->numberOfRecipesPerResult);
        $response->totalNumberOfResults = count($items);
        return new ClientResponse($response);
    }
}
