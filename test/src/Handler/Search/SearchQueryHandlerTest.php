<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Search;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\Api\Search\Collection\PaginatedResultCollection;
use FactorioItemBrowser\Api\Search\Entity\Query;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
use FactorioItemBrowser\Api\Search\SearchManagerInterface;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Search\SearchQueryHandler;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the SearchQueryHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Search\SearchQueryHandler
 */
class SearchQueryHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked mod service.
     * @var ModService&MockObject
     */
    protected $modService;

    /**
     * The mocked search decorator service.
     * @var SearchDecoratorService&MockObject
     */
    protected $searchDecoratorService;

    /**
     * The mocked search manager.
     * @var SearchManagerInterface&MockObject
     */
    protected $searchManager;

    /**
     * The mocked translation service.
     * @var TranslationService&MockObject
     */
    protected $translationService;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modService = $this->createMock(ModService::class);
        $this->searchDecoratorService = $this->createMock(SearchDecoratorService::class);
        $this->searchManager = $this->createMock(SearchManagerInterface::class);
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new SearchQueryHandler(
            $this->modService,
            $this->searchDecoratorService,
            $this->searchManager,
            $this->translationService
        );

        $this->assertSame($this->modService, $this->extractProperty($handler, 'modService'));
        $this->assertSame($this->searchDecoratorService, $this->extractProperty($handler, 'searchDecoratorService'));
        $this->assertSame($this->searchManager, $this->extractProperty($handler, 'searchManager'));
        $this->assertSame($this->translationService, $this->extractProperty($handler, 'translationService'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = SearchQueryRequest::class;

        $handler = new SearchQueryHandler(
            $this->modService,
            $this->searchDecoratorService,
            $this->searchManager,
            $this->translationService
        );
        $result = $this->invokeMethod($handler, 'getExpectedRequestClass');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the handleRequest method.
     * @throws ReflectionException
     * @covers ::handleRequest
     */
    public function testHandleRequest(): void
    {
        $query = 'abc';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;
        $numberOfRecipesPerResult = 1337;
        $enabledModCombinationIds = [13, 37];
        $currentLocale = 'def';
        $countResults = 7331;

        /* @var Query&MockObject $searchQuery */
        $searchQuery = $this->createMock(Query::class);

        $currentSearchResults = [
            $this->createMock(ResultInterface::class),
            $this->createMock(ResultInterface::class),
        ];
        $decoratedSearchResults = [
            $this->createMock(GenericEntityWithRecipes::class),
            $this->createMock(GenericEntityWithRecipes::class),
        ];

        $expectedResult = new SearchQueryResponse();
        $expectedResult->setResults($decoratedSearchResults)
                       ->setTotalNumberOfResults($countResults);

        /* @var SearchQueryRequest&MockObject $request */
        $request = $this->createMock(SearchQueryRequest::class);
        $request->expects($this->once())
                ->method('getQuery')
                ->willReturn($query);
        $request->expects($this->once())
                ->method('getIndexOfFirstResult')
                ->willReturn($indexOfFirstResult);
        $request->expects($this->once())
                ->method('getNumberOfResults')
                ->willReturn($numberOfResults);
        $request->expects($this->once())
                ->method('getNumberOfRecipesPerResult')
                ->willReturn($numberOfRecipesPerResult);

        /* @var PaginatedResultCollection&MockObject $searchResults */
        $searchResults = $this->createMock(PaginatedResultCollection::class);
        $searchResults->expects($this->once())
                      ->method('getResults')
                      ->with($this->identicalTo($indexOfFirstResult), $this->identicalTo($numberOfResults))
                      ->willReturn($currentSearchResults);
        $searchResults->expects($this->once())
                      ->method('count')
                      ->willReturn($countResults);

        $this->modService->expects($this->once())
                         ->method('getEnabledModCombinationIds')
                         ->willReturn($enabledModCombinationIds);

        $this->searchManager->expects($this->once())
                            ->method('parseQuery')
                            ->with(
                                $this->identicalTo($query),
                                $this->identicalTo($enabledModCombinationIds),
                                $this->identicalTo($currentLocale)
                            )
                            ->willReturn($searchQuery);
        $this->searchManager->expects($this->once())
                            ->method('search')
                            ->with($this->identicalTo($searchQuery))
                            ->willReturn($searchResults);

        $this->searchDecoratorService->expects($this->once())
                                     ->method('decorate')
                                     ->with(
                                         $this->identicalTo($currentSearchResults),
                                         $this->identicalTo($numberOfRecipesPerResult)
                                     )
                                     ->willReturn($decoratedSearchResults);

        $this->translationService->expects($this->once())
                                 ->method('getCurrentLocale')
                                 ->willReturn($currentLocale);

        $handler = new SearchQueryHandler(
            $this->modService,
            $this->searchDecoratorService,
            $this->searchManager,
            $this->translationService
        );
        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertEquals($expectedResult, $result);
    }
}
