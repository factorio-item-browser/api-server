<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Mod;

use BluePsyduck\MapperManager\MapperManagerInterface;
use FactorioItemBrowser\Api\Client\Transfer\Mod as ClientMod;
use FactorioItemBrowser\Api\Client\Request\Mod\ModListRequest;
use FactorioItemBrowser\Api\Client\Response\Mod\ModListResponse;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Database\Repository\ModRepository;
use FactorioItemBrowser\Api\Server\Handler\Mod\ModListHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the ModListHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Handler\Mod\ModListHandler
 */
class ModListHandlerTest extends TestCase
{
    /** @var MapperManagerInterface&MockObject */
    private MapperManagerInterface $mapperManager;
    /** @var ModRepository&MockObject */
    private ModRepository $modRepository;

    protected function setUp(): void
    {
        $this->mapperManager = $this->createMock(MapperManagerInterface::class);
        $this->modRepository = $this->createMock(ModRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return ModListHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): ModListHandler
    {
        return $this->getMockBuilder(ModListHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->mapperManager,
                        $this->modRepository,
                    ])
                    ->getMock();
    }

    public function testHandle(): void
    {
        $clientRequest = new ModListRequest();
        $clientRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);

        $databaseMod1 = $this->createMock(DatabaseMod::class);
        $databaseMod2 = $this->createMock(DatabaseMod::class);
        $clientMod1 = $this->createMock(ClientMod::class);
        $clientMod2 = $this->createMock(ClientMod::class);

        $expectedPayload = new ModListResponse();
        $expectedPayload->mods = [$clientMod1, $clientMod2];

        $this->modRepository->expects($this->once())
                            ->method('findByCombinationId')
                            ->with($this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')))
                            ->willReturn([$databaseMod1, $databaseMod2]);

        $this->mapperManager->expects($this->exactly(2))
                            ->method('map')
                            ->withConsecutive(
                                [$this->identicalTo($databaseMod1), $this->isInstanceOf(ClientMod::class)],
                                [$this->identicalTo($databaseMod2), $this->isInstanceOf(ClientMod::class)],
                            )
                            ->willReturnOnConsecutiveCalls(
                                $clientMod1,
                                $clientMod2,
                            );

        $instance = $this->createInstance();
        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }
}
