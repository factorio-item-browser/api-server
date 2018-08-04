<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Data\DataInterface;
use FactorioItemBrowser\Api\Server\Database\Service\AbstractModsAwareService;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AbstractModsAwareService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Service\AbstractModsAwareService
 */
class AbstractModsAwareServiceTest extends TestCase
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
        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        /* @var AbstractModsAwareService|MockObject $service */
        $service = $this->getMockBuilder(AbstractModsAwareService::class)
                        ->setMethods(['initializeRepositories'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $service->expects($this->once())
                ->method('initializeRepositories')
                ->with($entityManager);

        $service->__construct($entityManager, $modService);
        $this->assertSame($entityManager, $this->extractProperty($service, 'entityManager'));
        $this->assertSame($modService, $this->extractProperty($service, 'modService'));
    }

    /**
     * Tests the filterData method.
     * @covers ::filterData
     */
    public function testFilterData()
    {
        /* @var DataInterface|MockObject $data1 */
        $data1 = $this->getMockBuilder(DataInterface::class)
                      ->setMethods(['getOrder', 'getKeys'])
                      ->getMockForAbstractClass();
        $data1->expects($this->any())
              ->method('getOrder')
              ->willReturn(42);
        $data1->expects($this->any())
              ->method('getKeys')
              ->willReturn(['abc', 'def']);
        /* @var DataInterface|MockObject $data2 */
        $data2 = $this->getMockBuilder(DataInterface::class)
                      ->setMethods(['getOrder', 'getKeys'])
                      ->getMockForAbstractClass();
        $data2->expects($this->any())
              ->method('getOrder')
              ->willReturn(21);
        $data2->expects($this->any())
              ->method('getKeys')
              ->willReturn(['abc', 'def']);
        /* @var DataInterface|MockObject $data3 */
        $data3 = $this->getMockBuilder(DataInterface::class)
                      ->setMethods(['getOrder', 'getKeys'])
                      ->getMockForAbstractClass();
        $data3->expects($this->any())
              ->method('getOrder')
              ->willReturn(42);
        $data3->expects($this->any())
              ->method('getKeys')
              ->willReturn(['ghi', 'jkl']);

        $data = [$data1, $data2, $data3];
        $expectedResult = [$data1, $data3];

        /* @var AbstractModsAwareService $service */
        $service = $this->createMock(AbstractModsAwareService::class);
        $result = $this->invokeMethod($service, 'filterData', $data);
        $this->assertSame($expectedResult, $result);
    }
}
