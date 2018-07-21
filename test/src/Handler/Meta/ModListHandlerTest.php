<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Meta;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Server\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModListHandler;
use FactorioItemBrowser\Api\Server\Mapper\ModMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\InputFilter\InputFilter;

/**
 * The PHPUnit test of the ModListHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Mod\ModListHandler
 */
class ModListHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the createInputFilter method.
     * @covers ::createInputFilter
     */
    public function testCreateInputFilter()
    {
        /* @var ModMapper $modMapper */
        $modMapper = $this->createMock(ModMapper::class);
        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);
        /* @var TranslationService $translationService */
        $translationService = $this->createMock(TranslationService::class);

        $handler = new ModListHandler($modMapper, $modService, $translationService);
        $result = $this->invokeMethod($handler, 'createInputFilter');
        $this->assertInstanceOf(InputFilter::class, $result);
    }

    /**
     * Tests the handleRequest method.
     * @covers ::__construct
     * @covers ::handleRequest
     */
    public function testHandleRequest()
    {
        $requestData = new DataContainer([]);
        $enabledModNames = ['abc'];

        $databaseMod1 = new DatabaseMod('abc');
        $databaseMod2 = new DatabaseMod('def');

        /* @var ClientMod|MockObject $clientMod1 */
        $clientMod1 = $this->getMockBuilder(ClientMod::class)
                           ->setMethods(['setIsEnabled'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $clientMod1->expects($this->once())
                   ->method('setIsEnabled')
                   ->with(true);

        /* @var ClientMod|MockObject $clientMod2 */
        $clientMod2 = $this->getMockBuilder(ClientMod::class)
                           ->setMethods(['setIsEnabled'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $clientMod2->expects($this->once())
                   ->method('setIsEnabled')
                   ->with(false);

        $expectedResult = [
            'mods' => [
                $clientMod1,
                $clientMod2
            ]
        ];

        /* @var ModService|MockObject $modService */
        $modService = $this->getMockBuilder(ModService::class)
                           ->setMethods(['getEnabledModNames', 'getAllMods'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $modService->expects($this->once())
                   ->method('getEnabledModNames')
                   ->willReturn($enabledModNames);
        $modService->expects($this->once())
                   ->method('getAllMods')
                   ->willReturn([$databaseMod1, $databaseMod2]);

        /* @var ModMapper|MockObject $modMapper */
        $modMapper = $this->getMockBuilder(ModMapper::class)
                          ->setMethods(['mapMod'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $modMapper->expects($this->exactly(2))
                  ->method('mapMod')
                  ->withConsecutive(
                      [$databaseMod1],
                      [$databaseMod2]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $clientMod1,
                      $clientMod2
                  );

        /* @var TranslationService|MockObject $translationService */
        $translationService = $this->getMockBuilder(TranslationService::class)
                                   ->setMethods(['translateEntities'])
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $translationService->expects($this->once())
                           ->method('translateEntities');

        $handler = new ModListHandler($modMapper, $modService, $translationService);
        $result = $this->invokeMethod($handler, 'handleRequest', $requestData);
        $this->assertSame($expectedResult, $result);
    }
}
