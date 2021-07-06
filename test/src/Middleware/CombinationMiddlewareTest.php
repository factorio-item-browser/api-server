<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Middleware;

use FactorioItemBrowser\Api\Client\Request\Generic\GenericDetailsRequest;
use FactorioItemBrowser\Api\Database\Entity\Combination;
use FactorioItemBrowser\Api\Database\Repository\CombinationRepository;
use FactorioItemBrowser\Api\Server\Exception\CombinationNotFoundException;
use FactorioItemBrowser\Api\Server\Exception\ServerException;
use FactorioItemBrowser\Api\Server\Middleware\CombinationMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the CombinationMiddleware class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Middleware\CombinationMiddleware
 */
class CombinationMiddlewareTest extends TestCase
{
    /** @var CombinationRepository&MockObject */
    private CombinationRepository $combinationRepository;

    protected function setUp(): void
    {
        $this->combinationRepository = $this->createMock(CombinationRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return CombinationMiddleware&MockObject
     */
    private function createInstance(array $mockedMethods = []): CombinationMiddleware
    {
        return $this->getMockBuilder(CombinationMiddleware::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationRepository,
                    ])
                    ->getMock();
    }

    /**
     * @throws ServerException
     */
    public function testProcess(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $combination = $this->createMock(Combination::class);

        $clientRequest = new GenericDetailsRequest();
        $clientRequest->combinationId = $combinationId;

        $newRequest = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getParsedBody')
                ->willReturn($clientRequest);
        $request->expects($this->once())
                ->method('withAttribute')
                ->with($this->identicalTo(Combination::class), $this->identicalTo($combination))
                ->willReturn($newRequest);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($this->identicalTo($newRequest))
                ->willReturn($response);

        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo(Uuid::fromString($combinationId)))
                                    ->willReturn($combination);
        $this->combinationRepository->expects($this->once())
                                    ->method('updateLastUsageTime')
                                    ->with($this->identicalTo($combination));

        $instance = $this->createInstance();
        $result = $instance->process($request, $handler);

        $this->assertSame($response, $result);



        /*
        $clientRequest = $request->getParsedBody();
        try {
            $combinationId = Uuid::fromString($clientRequest->combinationId);
        } catch (Exception $e) {
            throw new CombinationNotFoundException($clientRequest->combinationId, $e);
        }

        $combination = $this->combinationRepository->findById(Uuid::fromString($clientRequest->combinationId));
        if ($combination === null) {
            throw new CombinationNotFoundException($combinationId->toString());
        }

        $this->combinationRepository->updateLastUsageTime($combination);
        $request = $request->withAttribute(Combination::class, $combination);
        return $handler->handle($request);
         */
    }

    /**
     * @throws ServerException
     */
    public function testProcessWithInvalidCombinationId(): void
    {
        $combinationId = 'foo';

        $clientRequest = new GenericDetailsRequest();
        $clientRequest->combinationId = $combinationId;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getParsedBody')
                ->willReturn($clientRequest);
        $request->expects($this->never())
                ->method('withAttribute');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
                ->method('handle');

        $this->combinationRepository->expects($this->never())
                                    ->method('findById');
        $this->combinationRepository->expects($this->never())
                                    ->method('updateLastUsageTime');

        $this->expectException(CombinationNotFoundException::class);

        $instance = $this->createInstance();
        $instance->process($request, $handler);
    }

    /**
     * @throws ServerException
     */
    public function testProcessWithUnknownCombination(): void
    {
        $combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';

        $clientRequest = new GenericDetailsRequest();
        $clientRequest->combinationId = $combinationId;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())
                ->method('getParsedBody')
                ->willReturn($clientRequest);
        $request->expects($this->never())
                ->method('withAttribute');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
                ->method('handle');

        $this->combinationRepository->expects($this->once())
                                    ->method('findById')
                                    ->with($this->equalTo(Uuid::fromString($combinationId)))
                                    ->willReturn(null);
        $this->combinationRepository->expects($this->never())
                                    ->method('updateLastUsageTime');

        $this->expectException(CombinationNotFoundException::class);

        $instance = $this->createInstance();
        $instance->process($request, $handler);
    }
}
