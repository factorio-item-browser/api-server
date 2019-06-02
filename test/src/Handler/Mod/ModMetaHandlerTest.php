<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Mod;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Request\Mod\ModMetaRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModMetaResponse;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModMetaHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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
     * The mocked mod repository.
     * @var ModRepository&MockObject
     */
    protected $modRepository;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modRepository = $this->createMock(ModRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new ModMetaHandler($this->modRepository);

        $this->assertSame($this->modRepository, $this->extractProperty($handler, 'modRepository'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = ModMetaRequest::class;

        $handler = new ModMetaHandler($this->modRepository);
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
        $enabledModCombinationIds = [42, 1337];
        $numberOfAvailableMods = 7331;
        $numberOfEnabledMods = 21;

        $expectedResult = new ModMetaResponse();
        $expectedResult->setNumberOfAvailableMods($numberOfAvailableMods)
                       ->setNumberOfEnabledMods($numberOfEnabledMods);

        /* @var ModMetaRequest&MockObject $request */
        $request = $this->createMock(ModMetaRequest::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getEnabledModCombinationIds')
                           ->willReturn($enabledModCombinationIds);

        $this->modRepository->expects($this->exactly(2))
                            ->method('count')
                            ->withConsecutive(
                                [$this->identicalTo([])],
                                [$this->identicalTo($enabledModCombinationIds)]
                            )
                            ->willReturnOnConsecutiveCalls(
                                $numberOfAvailableMods,
                                $numberOfEnabledMods
                            );

        /* @var ModMetaHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ModMetaHandler::class)
                        ->setMethods(['getAuthorizationToken'])
                        ->setConstructorArgs([$this->modRepository])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertEquals($expectedResult, $result);
    }
}
