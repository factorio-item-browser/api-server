<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Search;

use FactorioItemBrowser\Api\Client\Transfer\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Client\Request\Search\SearchQueryRequest;
use FactorioItemBrowser\Api\Client\Response\Search\SearchQueryResponse;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Entity\Mod;
use FactorioItemBrowser\Api\Search\Collection\PaginatedResultCollection;
use FactorioItemBrowser\Api\Search\Entity\Query;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
use FactorioItemBrowser\Api\Search\SearchManagerInterface;
use FactorioItemBrowser\Api\Server\Handler\Search\SearchQueryHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorService;
use FactorioItemBrowser\Api\Server\Service\TrackingService;
use FactorioItemBrowser\Api\Server\Tracking\Event\SearchEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the SearchQueryHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Handler\Search\SearchQueryHandler
 */
class SearchQueryHandlerTest extends TestCase
{
    /** @var SearchDecoratorService&MockObject */
    private SearchDecoratorService $searchDecoratorService;
    /** @var SearchManagerInterface&MockObject */
    private SearchManagerInterface $searchManager;
    /** @var TrackingService&MockObject */
    private TrackingService $trackingService;

    protected function setUp(): void
    {
        $this->searchDecoratorService = $this->createMock(SearchDecoratorService::class);
        $this->searchManager = $this->createMock(SearchManagerInterface::class);
        $this->trackingService = $this->createMock(TrackingService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return SearchQueryHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): SearchQueryHandler
    {
        return $this->getMockBuilder(SearchQueryHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->searchDecoratorService,
                        $this->searchManager,
                        $this->trackingService,
                    ])
                    ->getMock();
    }

    public function testHandle(): void
    {
        $query = 'abc';
        $indexOfFirstResult = 42;
        $numberOfResults = 21;
        $numberOfRecipesPerResult = 1337;
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $locale = 'def';
        $countResults = 7331;

        $combination = new Combination();
        $combination->getMods()->add(new Mod());
        $combination->getMods()->add(new Mod());
        $searchQuery = $this->createMock(Query::class);

        $currentSearchResults = [
            $this->createMock(ResultInterface::class),
            $this->createMock(ResultInterface::class),
        ];
        $decoratedSearchResults = [
            $this->createMock(GenericEntityWithRecipes::class),
            $this->createMock(GenericEntityWithRecipes::class),
        ];

        $expectedPayload = new SearchQueryResponse();
        $expectedPayload->results = $decoratedSearchResults;
        $expectedPayload->totalNumberOfResults = $countResults;

        $clientRequest = new SearchQueryRequest();
        $clientRequest->combinationId = $combinationId;
        $clientRequest->locale = $locale;
        $clientRequest->query = $query;
        $clientRequest->indexOfFirstResult = $indexOfFirstResult;
        $clientRequest->numberOfResults = $numberOfResults;
        $clientRequest->numberOfRecipesPerResult = $numberOfRecipesPerResult;

        $searchResults = $this->createMock(PaginatedResultCollection::class);
        $searchResults->expects($this->once())
                      ->method('getResults')
                      ->with($this->identicalTo($indexOfFirstResult), $this->identicalTo($numberOfResults))
                      ->willReturn($currentSearchResults);
        $searchResults->expects($this->once())
                      ->method('count')
                      ->willReturn($countResults);
        $searchResults->expects($this->any())
                      ->method('getIsCached')
                      ->willReturn(true);

        $expectedEvent = new SearchEvent();
        $expectedEvent->combinationId = $combinationId;
        $expectedEvent->modCount = 2;
        $expectedEvent->locale = $locale;
        $expectedEvent->queryString = $query;
        $expectedEvent->resultCount = $countResults;
        $expectedEvent->cached = true;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo(Combination::class))
                ->willReturn($combination);

        $this->searchManager->expects($this->once())
                            ->method('parseQuery')
                            ->with(
                                $this->equalTo(Uuid::fromString($combinationId)),
                                $this->identicalTo($locale),
                                $this->identicalTo($query)
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

        $this->trackingService->expects($this->once())
                              ->method('addEvent')
                              ->with($this->callback(function (SearchEvent $event) use ($expectedEvent): bool {
                                  $event->runtime = null; // Will most likely be 0 because rounding to milliseconds.

                                  $this->assertEquals($expectedEvent, $event);
                                  return true;
                              }));

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }
}
