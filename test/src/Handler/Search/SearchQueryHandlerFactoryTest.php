<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Search;

use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Search\SearchQueryHandler;
use FactorioItemBrowser\Api\Server\Handler\Search\SearchQueryHandlerFactory;
use FactorioItemBrowser\Api\Server\Search\Handler\SearchHandlerManager;
use FactorioItemBrowser\Api\Server\Search\SearchDecorator;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the SearchQueryHandlerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Search\SearchQueryHandlerFactory
 */
class SearchQueryHandlerFactoryTest extends TestCase
{
    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(4))
                  ->method('get')
                  ->withConsecutive(
                      [SearchHandlerManager::class],
                      [SearchDecorator::class],
                      [CachedSearchResultService::class],
                      [TranslationService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(SearchHandlerManager::class),
                      $this->createMock(SearchDecorator::class),
                      $this->createMock(CachedSearchResultService::class),
                      $this->createMock(TranslationService::class)
                  );

        $factory = new SearchQueryHandlerFactory();
        $result = $factory($container, SearchQueryHandler::class);
        $this->assertInstanceOf(SearchQueryHandler::class, $result);
    }
}
