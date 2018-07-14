<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Service;

use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Service\AbstractModsAwareServiceFactory;
use FactorioItemBrowser\Api\Server\Database\Service\IconService;
use FactorioItemBrowser\Api\Server\Database\Service\ItemService;
use FactorioItemBrowser\Api\Server\Database\Service\MachineService;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AbstractModsAwareServiceFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Service\AbstractModsAwareServiceFactory
 */
class AbstractModsAwareServiceFactoryTest extends TestCase
{
    /**
     * Provides the data for the invoke test.
     * @return array
     */
    public function provideInvoke(): array
    {
        return [
            [IconService::class],
            [ItemService::class],
            [MachineService::class],
            [RecipeService::class],
            [TranslationService::class],
        ];
    }

    /**
     * Tests the invoking.
     * @param string $className
     * @covers ::__invoke
     * @dataProvider provideInvoke
     */
    public function testInvoke(string $className)
    {
        /* @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)
                          ->setMethods(['get'])
                          ->getMockForAbstractClass();
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [EntityManager::class],
                      [ModService::class]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $this->createMock(EntityManager::class),
                      $this->createMock(ModService::class)
                  );

        $factory = new AbstractModsAwareServiceFactory();
        $result = $factory($container, $className);
        $this->assertInstanceOf($className, $result);
    }
}
