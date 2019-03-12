<?php

declare(strict_types=1);

/**
 * The interface of the search decorators.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace FactorioItemBrowser\Api\Server\SearchDecorator;

use FactorioItemBrowser\Api\Client\Entity\EntityInterface;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;

/**
 * The decorator for the items.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface SearchDecoratorInterface
{
    /**
     * Returns the result class supported by the decorator.
     * @return string
     */
    public function getSupportedResultClass(): string;

    /**
     * Initializes the decorator.
     * @param int $numberOfRecipesPerResult
     */
    public function initialize(int $numberOfRecipesPerResult): void;

    /**
     * Announces a search result to be decorated.
     * @param ResultInterface $searchResult
     */
    public function announce($searchResult): void;

    /**
     * Prepares the data for the actual decoration.
     */
    public function prepare(): void;

    /**
     * Actually decorates the search result.
     * @param ResultInterface $searchResult
     * @return EntityInterface|null
     */
    public function decorate($searchResult): ?EntityInterface;
}