<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Service;

use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
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
     * @var array|SearchDecoratorInterface[]
     */
    protected $searchDecoratorsByClass;

    /**
     * SearchDecoratorService constructor.
     * @param array|SearchDecoratorInterface[] $searchDecorators
     */
    public function __construct(array $searchDecorators)
    {
        foreach ($searchDecorators as $searchDecorator) {
            $this->searchDecoratorsByClass[$searchDecorator->getSupportedResultClass()] = $searchDecorator;
        }
    }

    /**
     * Decorates the search results to client entities.
     * @param array|ResultInterface[] $searchResults
     * @param int $numberOfRecipesPerResult
     * @return array|GenericEntityWithRecipes[]
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
     * @param int $numberOfRecipesPerResult
     */
    protected function initializeSearchDecorators(int $numberOfRecipesPerResult): void
    {
        foreach ($this->searchDecoratorsByClass as $searchDecorator) {
            $searchDecorator->initialize($numberOfRecipesPerResult);
        }
    }

    /**
     * Announces the search results to the search decorators.
     * @param array|ResultInterface[] $searchResults
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
     * @param array|ResultInterface[] $searchResults
     * @return array|GenericEntityWithRecipes[]
     */
    protected function decorateSearchResults(array $searchResults): array
    {
        $result = [];
        foreach ($searchResults as $searchResult) {
            $class = get_class($searchResult);
            if (isset($this->searchDecoratorsByClass[$class])) {
                $entity = $this->searchDecoratorsByClass[$class]->decorate($searchResult);
                if ($entity instanceof GenericEntityWithRecipes) {
                    $result[] = $entity;
                }
            }
        }
        return $result;
    }
}
