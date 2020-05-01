<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Item;

use FactorioItemBrowser\Api\Client\Request\Item\ItemListRequest;
use FactorioItemBrowser\Api\Client\Response\Item\ItemListResponse;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface as ClientResponseInterface;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;

/**
 * The handler of the /item/list request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ItemListHandler extends AbstractRequestHandler
{
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
     * @param ItemListRequest $clientRequest
     * @return ClientResponseInterface
     */
    protected function handleRequest($clientRequest): ClientResponseInterface
    {
        return new ItemListResponse();
    }
}
