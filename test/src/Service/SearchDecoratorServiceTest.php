<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntityWithRecipes;
use FactorioItemBrowser\Api\Search\Entity\Result\ResultInterface;
use FactorioItemBrowser\Api\Server\SearchDecorator\SearchDecoratorInterface;
use FactorioItemBrowser\Api\Server\Service\SearchDecoratorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the SearchDecoratorService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\SearchDecoratorService
 */
class SearchDecoratorServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var SearchDecoratorInterface&MockObject $searchDecorator1 */
        $searchDecorator1 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator1->expects($this->once())
                         ->method('getSupportedResultClass')
                         ->willReturn('abc');

        /* @var SearchDecoratorInterface&MockObject $searchDecorator2 */
        $searchDecorator2 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator2->expects($this->once())
                         ->method('getSupportedResultClass')
                         ->willReturn('def');

        $searchDecorators = [$searchDecorator1, $searchDecorator2];
        $expectedSearchDecorators = [
            'abc' => $searchDecorator1,
            'def' => $searchDecorator2,
        ];

        $service = new SearchDecoratorService($searchDecorators);

        $this->assertEquals($expectedSearchDecorators, $this->extractProperty($service, 'searchDecoratorsByClass'));
    }

    /**
     * Tests the decorate method.
     * @throws ReflectionException
     * @covers ::decorate
     */
    public function testDecorate(): void
    {
        $searchResults = [
            $this->createMock(ResultInterface::class),
            $this->createMock(ResultInterface::class),
        ];
        $numberOfRecipesPerResult = 42;
        $decoratedSearchResults = [
            $this->createMock(GenericEntityWithRecipes::class),
            $this->createMock(GenericEntityWithRecipes::class),
        ];

        /* @var SearchDecoratorService&MockObject $service */
        $service = $this->getMockBuilder(SearchDecoratorService::class)
                        ->setMethods([
                            'initializeSearchDecorators',
                            'announceSearchResults',
                            'prepareSearchDecorators',
                            'decorateSearchResults',
                        ])
                        ->disableOriginalConstructor()
                        ->getMock();
        $service->expects($this->once())
                ->method('initializeSearchDecorators')
                ->with($this->identicalTo($numberOfRecipesPerResult));
        $service->expects($this->once())
                ->method('announceSearchResults')
                ->with($this->identicalTo($searchResults));
        $service->expects($this->once())
                ->method('prepareSearchDecorators');
        $service->expects($this->once())
                ->method('decorateSearchResults')
                ->with($this->identicalTo($searchResults))
                ->willReturn($decoratedSearchResults);

        $result = $service->decorate($searchResults, $numberOfRecipesPerResult);

        $this->assertSame($decoratedSearchResults, $result);
    }

    /**
     * Tests the initializeSearchDecorators method.
     * @throws ReflectionException
     * @covers ::initializeSearchDecorators
     */
    public function testInitializeSearchDecorators(): void
    {
        $numberOfRecipesPerResult = 42;

        /* @var SearchDecoratorInterface&MockObject $searchDecorator1 */
        $searchDecorator1 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator1->expects($this->once())
                         ->method('initialize')
                         ->with($this->identicalTo($numberOfRecipesPerResult));

        /* @var SearchDecoratorInterface&MockObject $searchDecorator2 */
        $searchDecorator2 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator2->expects($this->once())
                         ->method('initialize')
                         ->with($this->identicalTo($numberOfRecipesPerResult));

        $searchDecorators = [$searchDecorator1, $searchDecorator2];

        $service = new SearchDecoratorService([]);
        $this->injectProperty($service, 'searchDecoratorsByClass', $searchDecorators);

        $this->invokeMethod($service, 'initializeSearchDecorators', $numberOfRecipesPerResult);
    }

    /**
     * Tests the announceSearchResults method.
     * @throws ReflectionException
     * @covers ::announceSearchResults
     */
    public function testAnnounceSearchResults(): void
    {
        $searchResultClass1 = 'MockedSearchResult1';
        $searchResultClass2 = 'MockedSearchResult2';

        /* @var ResultInterface&MockObject $searchResult1 */
        $searchResult1 = $this->getMockBuilder(ResultInterface::class)
                              ->setMockClassName($searchResultClass1)
                              ->getMockForAbstractClass();

        /* @var ResultInterface&MockObject $searchResult2 */
        $searchResult2 = $this->getMockBuilder(ResultInterface::class)
                              ->setMockClassName($searchResultClass2)
                              ->getMockForAbstractClass();

        $searchResults = [$searchResult1, $searchResult2];

        /* @var SearchDecoratorInterface&MockObject $searchDecorator1 */
        $searchDecorator1 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator1->expects($this->once())
                         ->method('announce')
                         ->with($this->identicalTo($searchResult1));

        /* @var SearchDecoratorInterface&MockObject $searchDecorator2 */
        $searchDecorator2 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator2->expects($this->once())
                         ->method('announce')
                         ->with($this->identicalTo($searchResult2));

        $searchDecorators = [
            $searchResultClass1 => $searchDecorator1,
            $searchResultClass2 => $searchDecorator2,
        ];

        $service = new SearchDecoratorService([]);
        $this->injectProperty($service, 'searchDecoratorsByClass', $searchDecorators);

        $this->invokeMethod($service, 'announceSearchResults', $searchResults);
    }

    /**
     * Tests the prepareSearchDecorators method.
     * @throws ReflectionException
     * @covers ::prepareSearchDecorators
     */
    public function testPrepareSearchDecorators(): void
    {
        /* @var SearchDecoratorInterface&MockObject $searchDecorator1 */
        $searchDecorator1 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator1->expects($this->once())
                         ->method('prepare');

        /* @var SearchDecoratorInterface&MockObject $searchDecorator2 */
        $searchDecorator2 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator2->expects($this->once())
                         ->method('prepare');

        $searchDecorators = [$searchDecorator1, $searchDecorator2];

        $service = new SearchDecoratorService([]);
        $this->injectProperty($service, 'searchDecoratorsByClass', $searchDecorators);

        $this->invokeMethod($service, 'prepareSearchDecorators');
    }

    /**
     * Tests the decorateSearchResults method.
     * @throws ReflectionException
     * @covers ::decorateSearchResults
     */
    public function testDecorateSearchResults(): void
    {
        $searchResultClass1 = 'MockedSearchResult1';
        $searchResultClass2 = 'MockedSearchResult2';

        /* @var GenericEntityWithRecipes&MockObject $entity1 */
        $entity1 = $this->createMock(GenericEntityWithRecipes::class);

        $expectedResult = [$entity1];

        /* @var ResultInterface&MockObject $searchResult1 */
        $searchResult1 = $this->getMockBuilder(ResultInterface::class)
                              ->setMockClassName($searchResultClass1)
                              ->getMockForAbstractClass();

        /* @var ResultInterface&MockObject $searchResult2 */
        $searchResult2 = $this->getMockBuilder(ResultInterface::class)
                              ->setMockClassName($searchResultClass2)
                              ->getMockForAbstractClass();

        $searchResults = [$searchResult1, $searchResult2];

        /* @var SearchDecoratorInterface&MockObject $searchDecorator1 */
        $searchDecorator1 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator1->expects($this->once())
                         ->method('decorate')
                         ->with($this->identicalTo($searchResult1))
                         ->willReturn($entity1);

        /* @var SearchDecoratorInterface&MockObject $searchDecorator2 */
        $searchDecorator2 = $this->createMock(SearchDecoratorInterface::class);
        $searchDecorator2->expects($this->once())
                         ->method('decorate')
                         ->with($this->identicalTo($searchResult2))
                         ->willReturn(null);

        $searchDecorators = [
            $searchResultClass1 => $searchDecorator1,
            $searchResultClass2 => $searchDecorator2,
        ];

        $service = new SearchDecoratorService([]);
        $this->injectProperty($service, 'searchDecoratorsByClass', $searchDecorators);

        $result = $this->invokeMethod($service, 'decorateSearchResults', $searchResults);

        $this->assertEquals($expectedResult, $result);
    }
}
