<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Server\Database\Service\AbstractDatabaseService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AbstractDatabaseService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Service\AbstractDatabaseService
 */
class AbstractDatabaseServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     */
    public function testConstruct()
    {
        /* @var EntityManager $entityManager */
        $entityManager = $this->createMock(EntityManager::class);

        /* @var AbstractDatabaseService|MockObject $service */
        $service = $this->getMockBuilder(AbstractDatabaseService::class)
                        ->setMethods(['initializeRepositories'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $service->expects($this->once())
                ->method('initializeRepositories')
                ->with($entityManager);

        $service->__construct($entityManager);
        $this->assertSame($entityManager, $this->extractProperty($service, 'entityManager'));
    }
}
