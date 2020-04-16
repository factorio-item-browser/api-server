<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Mod;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModListHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

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
     * The mocked mapper manager.
     * @var MapperManagerInterface&MockObject
     */
    protected $mapperManager;

    /**
     * The mocked mod repository.
     * @var ModRepository&MockObject
     */
    protected $modRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->modRepository = $this->createMock(ModRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new ModListHandler($this->mapperManager, $this->modRepository);

        $this->assertSame($this->mapperManager, $this->extractProperty($handler, 'mapperManager'));
        $this->assertSame($this->modRepository, $this->extractProperty($handler, 'modRepository'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = ModListRequest::class;

        $handler = new ModListHandler($this->mapperManager, $this->modRepository);
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
        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);

        /* @var ModListRequest&MockObject $request */
        $request = $this->createMock(ModListRequest::class);
        /* @var DatabaseMod&MockObject $databaseMod1 */
        $databaseMod1 = $this->createMock(DatabaseMod::class);
        /* @var DatabaseMod&MockObject $databaseMod2 */
        $databaseMod2 = $this->createMock(DatabaseMod::class);
        /* @var ClientMod&MockObject $clientMod1 */
        $clientMod1 = $this->createMock(ClientMod::class);
        /* @var ClientMod&MockObject $clientMod2 */
        $clientMod2 = $this->createMock(ClientMod::class);

        $expectedResult = new ModListResponse();
        $expectedResult->setMods([$clientMod1, $clientMod2]);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $this->modRepository->expects($this->once())
                            ->method('findByCombinationId')
                            ->with($this->identicalTo($combinationId))
                            ->willReturn([$databaseMod1, $databaseMod2]);

        /* @var ModListHandler&MockObject $handler */
        $handler = $this->getMockBuilder(ModListHandler::class)
                        ->onlyMethods(['getAuthorizationToken', 'createClientMod'])
                        ->setConstructorArgs([$this->mapperManager, $this->modRepository])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->exactly(2))
                ->method('createClientMod')
                ->withConsecutive(
                    [$this->identicalTo($databaseMod1)],
                    [$this->identicalTo($databaseMod2)]
                )
                ->willReturnOnConsecutiveCalls(
                    $clientMod1,
                    $clientMod2
                );

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createClientMod method.
     * @throws ReflectionException
     * @covers ::createClientMod
     */
    public function testCreateClientMod(): void
    {
        $expectedResult = new ClientMod();

        /* @var DatabaseMod&MockObject $databaseMod */
        $databaseMod = $this->createMock(DatabaseMod::class);

        $this->mapperManager->expects($this->once())
                            ->method('map')
                            ->with($this->identicalTo($databaseMod), $this->isInstanceOf(ClientMod::class));

        $handler = new ModListHandler($this->mapperManager, $this->modRepository);
        $result = $this->invokeMethod($handler, 'createClientMod', $databaseMod);

        $this->assertEquals($expectedResult, $result);
    }
}
