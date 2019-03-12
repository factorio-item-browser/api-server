<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Search;

use FactorioItemBrowser\Api\Client\Request\RequestInterface;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\ResponseInterface;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\Api\Search\SearchManagerInterface;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
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
     * The mod service.
     * @var ModService
     */
    protected $modService;

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
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the request handler.
     * @param ModService $modService
     * @param SearchDecoratorService $searchDecoratorService
     * @param SearchManagerInterface $searchManager
     * @param TranslationService $translationService
     */
    public function __construct(
        ModService $modService,
        SearchDecoratorService $searchDecoratorService,
        SearchManagerInterface $searchManager,
        TranslationService $translationService
    ) {
        $this->modService = $modService;
        $this->searchDecoratorService = $searchDecoratorService;
        $this->searchManager = $searchManager;
        $this->translationService = $translationService;
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
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function handleRequest(RequestInterface $request): ResponseInterface
    {
        /** @var SearchQueryRequest $request */

        $searchQuery = $this->searchManager->parseQuery(
            $request->getQuery(),
            $this->modService->getEnabledModCombinationIds(),
            $this->translationService->getCurrentLocale()
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
