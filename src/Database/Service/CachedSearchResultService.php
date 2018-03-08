<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Database\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Entity\CachedSearchResult;
use FactorioItemBrowser\Api\Server\Database\Repository\CachedSearchResultRepository;
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
        $cachedSearchResult = $this->cachedSearchResultRepository->findByHash($this->buildSearchHash($searchQuery));
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
    ): CachedResultCollection
    {
        $resultDataArray = [];
        foreach ($resultCollection->getResults() as $result) {
            $ids = array_merge(
                [$result instanceof RecipeResult ? 0 : $result->getId()],
                $result->getRecipeIds()
            );
            $resultDataArray[] = implode(',', $ids);
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
     * @return int
     */
    protected function buildSearchHash(SearchQuery $searchQuery): int
    {
        return crc32(json_encode([
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
            $recipeIds = explode(',', $recipeIdData);
            $itemId = (int) array_shift($recipeIds);
            if ($itemId > 0) {
                $result = new ItemResult();
                $result->setId($itemId);
            } else {
                $result = new RecipeResult();
            }
            $result->setRecipeIds($recipeIds);
            $cachedResultCollection->add($result);
        }
        return $cachedResultCollection;
    }
}