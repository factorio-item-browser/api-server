<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Client\Request\Item\ItemIngredientRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemIngredientResponse;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler of the /item/ingredient request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemIngredientHandler extends AbstractItemHandler implements RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ServerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ItemIngredientRequest $clientRequest */
        $clientRequest = $request->getParsedBody();

        $combinationId = Uuid::fromString($clientRequest->combinationId);
        $item = $this->fetchItem($combinationId, $clientRequest->type, $clientRequest->name);
        $recipeData = $this->recipeService->getDataWithIngredients($combinationId, [$item]);

        $response = new ItemIngredientResponse();
        $response->item = $this->createItem(
            $item,
            $recipeData,
            $clientRequest->numberOfResults,
            $clientRequest->indexOfFirstResult,
        );
        return new ClientResponse($response);
    }
}
