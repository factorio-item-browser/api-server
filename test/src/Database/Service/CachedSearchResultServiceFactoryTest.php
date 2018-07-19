<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultServiceFactory;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the CachedSearchResultServiceFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultServiceFactory
 */
class CachedSearchResultServiceFactoryTest extends TestCase
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
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(
                      [EntityManager::class],
                      [ModService::class],
                      [TranslationService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(EntityManager::class),
                      $this->createMock(ModService::class),
                      $this->createMock(TranslationService::class)
                  );

        $factory = new CachedSearchResultServiceFactory();
        $result = $factory($container, CachedSearchResultService::class);
        $this->assertInstanceOf(CachedSearchResultService::class, $result);
    }
}
