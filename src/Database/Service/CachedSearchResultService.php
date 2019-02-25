<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use DateTime;
use FactorioItemBrowser\Api\Database\Repository\CachedSearchResultRepository;

/**
 * The service class of the cached search result database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CachedSearchResultService extends AbstractModsAwareService implements CleanableServiceInterface
{
    /**
     * The maximal number of search results to allow.
     */
    protected const MAX_SEARCH_RESULTS = 1000;

    /**
     * The maximal age of the search results in the cache, in seconds.
     */
    protected const MAX_AGE_SECONDS = 3600;

    /**
     * The repository of the cached search results.
     * @var CachedSearchResultRepository
     */
    protected $cachedSearchResultRepository;

    /**
     * Initializes the service.
     * @param CachedSearchResultRepository $cachedSearchResultRepository
     * @param ModService $modService
     */
    public function __construct(
        CachedSearchResultRepository $cachedSearchResultRepository,
        ModService $modService
    ) {
        parent::__construct($modService);

        $this->cachedSearchResultRepository = $cachedSearchResultRepository;
    }

    /**
     * Cleans up the database table.
     */
    public function cleanup(): void
    {
        $this->cachedSearchResultRepository->cleanup($this->getMaxAge());
    }

    /**
     * Returns the maximal age to use for the cache.
     * @return DateTime
     */
    protected function getMaxAge(): DateTime
    {
        return new DateTime('-' . self::MAX_AGE_SECONDS . ' seconds');
    }
}
