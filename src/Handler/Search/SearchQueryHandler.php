<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Search;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Search\SearchManagerInterface;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorService;
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
        $queryString = $requestData->getString('query');
        $numberOfResults = $requestData->getInteger('numberOfResults');
        $indexOfFirstResult = $requestData->getInteger('indexOfFirstResult');
        $numberOfRecipesPerResult = $requestData->getInteger('numberOfRecipesPerResult');

        $searchQuery = $this->searchManager->parseQuery(
            $queryString,
            $this->modService->getEnabledModCombinationIds(),
            $this->translationService->getCurrentLocale()
        );
        $searchResults = $this->searchManager->search($searchQuery);

        $results = $this->searchDecoratorService->decorate(
            $searchResults->getResults($indexOfFirstResult, $numberOfResults),
            $numberOfRecipesPerResult
        );

        $this->translationService->translateEntities();
        return [
            'results' => $results,
            'totalNumberOfResults' => $searchResults->count()
        ];
    }
}
