<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Search;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Search\Handler\SearchHandlerManager;
use FactorioItemBrowser\Api\Server\Search\Result\CachedResultCollection;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchDecorator;
use FactorioItemBrowser\Api\Server\Search\SearchQueryParser;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The handler of the /search/query request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchQueryHandler extends AbstractRequestHandler
{
    /**
     * The search handler manager.
     * @var SearchHandlerManager
     */
    protected $searchHandlerManager;

    /**
     * The search decorator.
     * @var SearchDecorator
     */
    protected $searchDecorator;

    /**
     * The database cached search result service.
     * @var CachedSearchResultService
     */
    protected $cachedSearchResultService;

    /**
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the request handler.
     * @param SearchHandlerManager $searchHandlerManager
     * @param SearchDecorator $searchDecorator
     * @param CachedSearchResultService $cachedSearchResultService
     * @param TranslationService $translationService
     */
    public function __construct(
        SearchHandlerManager $searchHandlerManager,
        SearchDecorator $searchDecorator,
        CachedSearchResultService $cachedSearchResultService,
        TranslationService $translationService
    ) {
        $this->searchHandlerManager = $searchHandlerManager;
        $this->searchDecorator = $searchDecorator;
        $this->cachedSearchResultService = $cachedSearchResultService;
        $this->translationService = $translationService;
    }

    /**
     * Creates the input filter to use to verify the request.
     * @return InputFilter
     */
    protected function createInputFilter(): InputFilter
    {
        $inputFilter = new InputFilter();
        $inputFilter
            ->add([
                'name' => 'query',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'numberOfResults',
                'required' => true,
                'fallback_value' => 10,
                'filters' => [
                    new ToInt()
                ],
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'indexOfFirstResult',
                'required' => true,
                'fallback_value' => 0,
                'filters' => [
                    new ToInt()
                ],
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'numberOfRecipesPerResult',
                'required' => true,
                'fallback_value' => 3,
                'filters' => [
                    new ToInt()
                ],
                'validators' => [
                    new NotEmpty()
                ]
            ]);

        return $inputFilter;
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $searchQuery = (new SearchQueryParser())->parse($requestData->getString('query'));
        $numberOfResults = $requestData->getInteger('numberOfResults');
        $indexOfFirstResult = $requestData->getInteger('indexOfFirstResult');
        $numberOfRecipesPerResult = $requestData->getInteger('numberOfRecipesPerResult');

        $cachedSearchResults = $this->cachedSearchResultService->getSearchResults($searchQuery);
        if (true || !$cachedSearchResults instanceof CachedResultCollection) {
            $searchResults = new ResultCollection();
            $this->searchHandlerManager->handle($searchQuery, $searchResults);
            $searchResults->sort();

            $cachedSearchResults = $this->cachedSearchResultService->persistSearchResults($searchQuery, $searchResults);
        }

        $results = $this->searchDecorator->decorate(
            $cachedSearchResults->getResults($numberOfResults, $indexOfFirstResult),
            $numberOfRecipesPerResult
        );

        $this->translationService->translateEntities();
        return [
            'results' => $results,
            'totalNumberOfResults' => $cachedSearchResults->count()
        ];
    }
}
