<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;

/**
 * The interface of the search decorators.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @template TResult of ResultInterface
 */
interface SearchDecoratorInterface
{
    /**
     * Returns the result class supported by the decorator.
     * @return class-string<TResult>
     */
    public function getSupportedResultClass(): string;

    /**
     * Initializes the decorator.
     * @param int $numberOfRecipesPerResult
     */
    public function initialize(int $numberOfRecipesPerResult): void;

    /**
     * Announces a search result to be decorated.
     * @param TResult $searchResult
     */
    public function announce(ResultInterface $searchResult): void;

    /**
     * Prepares the data for the actual decoration.
     */
    public function prepare(): void;

    /**
     * Actually decorates the search result.
     * @param TResult $searchResult
     * @return GenericEntityWithRecipes|null
     */
    public function decorate(ResultInterface $searchResult): ?GenericEntityWithRecipes;
}
