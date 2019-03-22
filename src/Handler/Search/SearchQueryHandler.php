<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Search;

use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\Api\Search\SearchManagerInterface;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorService;

/**
 * The handler of the /search/query request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchQueryHandler extends AbstractRequestHandler
{
    /**
     * The search decorator service.
     * @var SearchDecoratorService
     */
    protected $searchDecoratorService;

    /**
     * The search manager.
     * @var SearchManagerInterface
     */
    protected $searchManager;

    /**
     * Initializes the request handler.
     * @param SearchDecoratorService $searchDecoratorService
     * @param SearchManagerInterface $searchManager
     */
    public function __construct(
        SearchDecoratorService $searchDecoratorService,
        SearchManagerInterface $searchManager
    ) {
        $this->searchDecoratorService = $searchDecoratorService;
        $this->searchManager = $searchManager;
    }

    /**
     * Returns the request class the handler is expecting.
     * @return string
     */
    protected function getExpectedRequestClass(): string
    {
        return SearchQueryRequest::class;
    }

    /**
     * Creates the response data from the validated request data.
     * @param SearchQueryRequest $request
     * @return ResponseInterface
     */
    protected function handleRequest($request): ResponseInterface
    {
        $authorizationToken = $this->getAuthorizationToken();

        $searchQuery = $this->searchManager->parseQuery(
            $request->getQuery(),
            $authorizationToken->getEnabledModCombinationIds(),
            $authorizationToken->getLocale()
        );
        $searchResults = $this->searchManager->search($searchQuery);
        $currentSearchResults = $searchResults->getResults(
            $request->getIndexOfFirstResult(),
            $request->getNumberOfResults()
        );
        $decoratedSearchResults = $this->searchDecoratorService->decorate(
            $currentSearchResults,
            $request->getNumberOfRecipesPerResult()
        );

        $response = new SearchQueryResponse();
        $response->setResults($decoratedSearchResults)
                 ->setTotalNumberOfResults($searchResults->count());
        return $response;
    }
}
