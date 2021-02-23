<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Search;

use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\Api\Search\SearchManagerInterface;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The handler of the /search/query request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchQueryHandler implements RequestHandlerInterface
{
    private SearchDecoratorService $searchDecoratorService;
    private SearchManagerInterface $searchManager;

    public function __construct(
        SearchDecoratorService $searchDecoratorService,
        SearchManagerInterface $searchManager
    ) {
        $this->searchDecoratorService = $searchDecoratorService;
        $this->searchManager = $searchManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var SearchQueryRequest $clientRequest */
        $clientRequest = $request->getParsedBody();

        $searchQuery = $this->searchManager->parseQuery(
            Uuid::fromString($clientRequest->combinationId),
            $clientRequest->locale,
            $clientRequest->query,
        );
        $searchResults = $this->searchManager->search($searchQuery);
        $currentSearchResults = $searchResults->getResults(
            $clientRequest->indexOfFirstResult,
            $clientRequest->numberOfResults,
        );
        $decoratedSearchResults = $this->searchDecoratorService->decorate(
            $currentSearchResults,
            $clientRequest->numberOfRecipesPerResult,
        );

        $response = new SearchQueryResponse();
        $response->results = $decoratedSearchResults; // @phpstan-ignore-line
        $response->totalNumberOfResults = $searchResults->count();
        return new ClientResponse($response);
    }
}
