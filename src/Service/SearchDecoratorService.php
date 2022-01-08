<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
use FactorioItemBrowser\Api\Server\Constant\ConfigKey;
use FactorioItemBrowser\Api\Server\SearchDecorator\SearchDecoratorInterface;

/**
 * The service for decorating the search results.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class SearchDecoratorService
{
    /**
     * The search decorators by their supported result class.
     * @var array<class-string, SearchDecoratorInterface<ResultInterface>>
     */
    protected array $searchDecoratorsByClass;

    /**
     * @param array<SearchDecoratorInterface<ResultInterface>> $searchDecorators
     */
    public function __construct(
        #[InjectAliasArray(ConfigKey::MAIN, ConfigKey::SEARCH_DECORATORS)]
        array $searchDecorators,
    ) {
        foreach ($searchDecorators as $searchDecorator) {
            $this->searchDecoratorsByClass[$searchDecorator->getSupportedResultClass()] = $searchDecorator;
        }
    }

    /**
     * Decorates the search results to client entities.
     * @param array<ResultInterface> $searchResults
     * @return array<GenericEntity>
     */
    public function decorate(array $searchResults, int $numberOfRecipesPerResult): array
    {
        $this->initializeSearchDecorators($numberOfRecipesPerResult);
        $this->announceSearchResults($searchResults);
        $this->prepareSearchDecorators();
        return $this->decorateSearchResults($searchResults);
    }

    /**
     * Initializes all the search decorators.
     */
    protected function initializeSearchDecorators(int $numberOfRecipesPerResult): void
    {
        foreach ($this->searchDecoratorsByClass as $searchDecorator) {
            $searchDecorator->initialize($numberOfRecipesPerResult);
        }
    }

    /**
     * Announces the search results to the search decorators.
     * @param array<ResultInterface> $searchResults
     */
    protected function announceSearchResults(array $searchResults): void
    {
        foreach ($searchResults as $searchResult) {
            $class = get_class($searchResult);
            if (isset($this->searchDecoratorsByClass[$class])) {
                $this->searchDecoratorsByClass[$class]->announce($searchResult);
            }
        }
    }

    /**
     * Prepares all the search decorators.
     */
    protected function prepareSearchDecorators(): void
    {
        foreach ($this->searchDecoratorsByClass as $searchDecorator) {
            $searchDecorator->prepare();
        }
    }

    /**
     * Actually decorates the search results through the search decorators.
     * @param array<ResultInterface> $searchResults
     * @return array<GenericEntity>
     */
    protected function decorateSearchResults(array $searchResults): array
    {
        $result = [];
        foreach ($searchResults as $searchResult) {
            $class = get_class($searchResult);
            if (isset($this->searchDecoratorsByClass[$class])) {
                $entity = $this->searchDecoratorsByClass[$class]->decorate($searchResult);
                if ($entity instanceof GenericEntity) {
                    $result[] = $entity;
                }
            }
        }
        return $result;
    }
}
