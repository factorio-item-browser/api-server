<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Entity\CachedSearchResult;
use FactorioItemBrowser\Api\Database\Repository\CachedSearchResultRepository;
use FactorioItemBrowser\Api\Server\Search\Result\AbstractResult;
use FactorioItemBrowser\Api\Server\Search\Result\CachedResultCollection;
use FactorioItemBrowser\Api\Server\Search\Result\ItemResult;
use FactorioItemBrowser\Api\Server\Search\Result\RecipeResult;
use FactorioItemBrowser\Api\Server\Search\Result\ResultCollection;
use FactorioItemBrowser\Api\Server\Search\SearchQuery;

/**
 * The service class of the cached search result database table.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CachedSearchResultService extends AbstractModsAwareService
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
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * The repository of the cached search results.
     * @var CachedSearchResultRepository
     */
    protected $cachedSearchResultRepository;

    /**
     * Initializes the service.
     * @param EntityManager $entityManager
     * @param ModService $modService
     * @param TranslationService $translationService
     */
    public function __construct(
        EntityManager $entityManager,
        ModService $modService,
        TranslationService $translationService
    ) {
        parent::__construct($entityManager, $modService);
        $this->translationService = $translationService;
    }

    /**
     * Initializes the repositories needed by the service.
     * @param EntityManager $entityManager
     * @return $this
     */
    protected function initializeRepositories(EntityManager $entityManager)
    {
        $this->cachedSearchResultRepository = $entityManager->getRepository(CachedSearchResult::class);
        return $this;
    }

    /**
     * Returns the cached search results, if available.
     * @param SearchQuery $searchQuery
     * @return CachedResultCollection|null
     */
    public function getSearchResults(SearchQuery $searchQuery): ?CachedResultCollection
    {
        $result = null;
        $cachedSearchResults = $this->cachedSearchResultRepository->findByHashes(
            [$this->buildSearchHash($searchQuery)],
            $this->getMaxAge()
        );
        $cachedSearchResult = array_shift($cachedSearchResults);
        if ($cachedSearchResult instanceof CachedSearchResult) {
            $result = $this->createResultCollectionFromData($cachedSearchResult->getResultData());
            $cachedSearchResult->setLastSearchTime(new DateTime());
            $this->entityManager->flush($cachedSearchResult);
        }
        return $result;
    }

    /**
     * Persists the search results into the cache.
     * @param SearchQuery $searchQuery
     * @param ResultCollection $resultCollection
     * @return CachedResultCollection
     */
    public function persistSearchResults(
        SearchQuery $searchQuery,
        ResultCollection $resultCollection
    ): CachedResultCollection {
        $resultDataArray = [];
        foreach (array_slice($resultCollection->getResults(), 0, self::MAX_SEARCH_RESULTS) as $result) {
            /* @var AbstractResult $result */
            if ($result->getId() > 0 || count($result->getGroupedRecipeIds()) > 0) {
                $ids = [$result->getId()];
                foreach ($result->getGroupedRecipeIds() as $groupedRecipeIds) {
                    $ids[] = implode('+', $groupedRecipeIds);
                }
                $resultDataArray[] = implode(',', $ids);
            }
        }
        $resultData = implode('|', $resultDataArray);

        $cachedSearchResult = new CachedSearchResult($this->buildSearchHash($searchQuery));
        $cachedSearchResult->setResultData($resultData);
        $cachedSearchResult = $this->entityManager->merge($cachedSearchResult);
        $this->entityManager->flush($cachedSearchResult);

        return $this->createResultCollectionFromData($resultData);
    }

    /**
     * Calculates and returns the hash to use for the search results.
     * @param SearchQuery $searchQuery
     * @return string
     */
    protected function buildSearchHash(SearchQuery $searchQuery): string
    {
        return hash('crc32b', (string) json_encode([
            'queryHash' => $searchQuery->getHash(),
            'enabledMods' => $this->modService->getEnabledModCombinationIds(),
            'locale' => $this->translationService->getCurrentLocale()
        ]));
    }

    /**
     * Creates a cached result collection from the specified result data.
     * @param string $resultData
     * @return CachedResultCollection
     */
    protected function createResultCollectionFromData(string $resultData): CachedResultCollection
    {
        $cachedResultCollection = new CachedResultCollection();

        foreach (explode('|', $resultData) as $recipeIdData) {
            if (strlen($recipeIdData) > 0) {
                $recipeIds = explode(',', $recipeIdData);
                $itemId = (int) array_shift($recipeIds);
                if ($itemId > 0) {
                    $result = new ItemResult();
                    $result->setId($itemId);
                } else {
                    $result = new RecipeResult();
                }
                foreach ($recipeIds as $index => $recipeIdGroup) {
                    $result->addRecipeIds((string) $index, array_map('intval', explode('+', $recipeIdGroup)));
                }
                $cachedResultCollection->add($result);
            }
        }
        return $cachedResultCollection;
    }

    /**
     * Cleans up the database table.
     * @return $this
     */
    public function cleanup()
    {
        $this->cachedSearchResultRepository->cleanup($this->getMaxAge());
        return $this;
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
