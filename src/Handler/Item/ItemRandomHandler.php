<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Client\Request\Item\ItemRandomRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemRandomResponse;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler of the /item/random request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemRandomHandler extends AbstractItemHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ItemRandomRequest $clientRequest */
        $clientRequest = $request->getParsedBody();

        $combinationId = Uuid::fromString($clientRequest->combinationId);
        $items = $this->itemRepository->findRandom($combinationId, $clientRequest->numberOfResults);

        $response = new ItemRandomResponse();
        $response->items = $this->mapItems($combinationId, $items, $clientRequest->numberOfRecipesPerResult);
        return new ClientResponse($response);
    }
}
