<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Mod;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModMetaHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\InputFilter\InputFilter;

/**
 * The PHPUnit test of the ModMetaHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Mod\ModMetaHandler
 */
class ModMetaHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the createInputFilter method.
     * @covers ::createInputFilter
     */
    public function testCreateInputFilter(): void
    {
        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);

        $handler = new ModMetaHandler($modService);
        $result = $this->invokeMethod($handler, 'createInputFilter');
        $this->assertInstanceOf(InputFilter::class, $result);
    }

    /**
     * Tests the handleRequest method.
     * @covers ::__construct
     * @covers ::handleRequest
     */
    public function testHandleRequest(): void
    {
        $requestData = new DataContainer([]);
        $expectedResult = [
            'numberOfAvailableMods' => 42,
            'numberOfEnabledMods' => 21
        ];

        /* @var ModService|MockObject $modService */
        $modService = $this->getMockBuilder(ModService::class)
                           ->setMethods(['getNumberOfAvailableMods', 'getNumberOfEnabledMods'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $modService->expects($this->once())
                   ->method('getNumberOfAvailableMods')
                   ->willReturn(42);
        $modService->expects($this->once())
                   ->method('getNumberOfEnabledMods')
                   ->willReturn(21);

        $handler = new ModMetaHandler($modService);
        $result = $this->invokeMethod($handler, 'handleRequest', $requestData);
        $this->assertSame($expectedResult, $result);
    }
}
