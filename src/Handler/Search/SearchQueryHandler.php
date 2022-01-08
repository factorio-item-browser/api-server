<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Search;

use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Search\SearchManagerInterface;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorService;
use FactorioItemBrowser\Api\Server\Service\TrackingService;
use FactorioItemBrowser\Api\Server\Tracking\Event\SearchEvent;
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
    public function __construct(
        private readonly SearchDecoratorService $searchDecoratorService,
        private readonly SearchManagerInterface $searchManager,
        private readonly TrackingService $trackingService,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var SearchQueryRequest $clientRequest */
        $clientRequest = $request->getParsedBody();
        /** @var Combination $combination */
        $combination = $request->getAttribute(Combination::class);

        $startTime = microtime(true);

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

        $trackingEvent = new SearchEvent();
        $trackingEvent->combinationId = $clientRequest->combinationId;
        $trackingEvent->modCount = $combination->getMods()->count();
        $trackingEvent->locale = $clientRequest->locale;
        $trackingEvent->queryString = $clientRequest->query;
        $trackingEvent->resultCount = $response->totalNumberOfResults;
        $trackingEvent->runtime = round((microtime(true) - $startTime) * 1000);
        $trackingEvent->cached = $searchResults->getIsCached();
        $this->trackingService->addEvent($trackingEvent);

        return new ClientResponse($response);
    }
}
