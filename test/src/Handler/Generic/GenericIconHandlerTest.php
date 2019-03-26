<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Entity\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\Api\Database\Data\IconData;
use FactorioItemBrowser\Api\Database\Entity\IconFile;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericIconHandler;
use FactorioItemBrowser\Api\Server\Service\IconService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the GenericIconHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Handler\Generic\GenericIconHandler
 */
class GenericIconHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked icon service.
     * @var IconService&MockObject
     */
    protected $iconService;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->iconService = $this->createMock(IconService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new GenericIconHandler($this->iconService);

        $this->assertSame($this->iconService, $this->extractProperty($handler, 'iconService'));
    }

    /**
     * Tests the getExpectedRequestClass method.
     * @throws ReflectionException
     * @covers ::getExpectedRequestClass
     */
    public function testGetExpectedRequestClass(): void
    {
        $expectedResult = GenericIconRequest::class;

        $handler = new GenericIconHandler($this->iconService);
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
        $namesByTypes = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];
        $iconFileHashes = ['pqr', 'stu'];

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        /* @var GenericIconResponse&MockObject $response */
        $response = $this->createMock(GenericIconResponse::class);

        $entities = [
            $this->createMock(Entity::class),
            $this->createMock(Entity::class),
        ];
        $clientIcons = [
            $this->createMock(ClientIcon::class),
            $this->createMock(ClientIcon::class),
        ];
        $filteredClientIcons = [
            $this->createMock(ClientIcon::class),
            $this->createMock(ClientIcon::class),
        ];

        /* @var GenericIconRequest&MockObject $request */
        $request = $this->createMock(GenericIconRequest::class);
        $request->expects($this->once())
                ->method('getEntities')
                ->willReturn($entities);

        $this->iconService->expects($this->once())
                          ->method('injectAuthorizationToken')
                          ->with($this->identicalTo($authorizationToken));

        /* @var GenericIconHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericIconHandler::class)
                        ->setMethods([
                            'getAuthorizationToken',
                            'extractTypesAndNames',
                            'fetchIconFileHashes',
                            'fetchIcons',
                            'filterRequestedIcons',
                            'hydrateContentToIcons',
                            'createResponse',
                        ])
                        ->setConstructorArgs([$this->iconService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('getAuthorizationToken')
                ->willReturn($authorizationToken);
        $handler->expects($this->once())
                ->method('extractTypesAndNames')
                ->with($this->identicalTo($entities))
                ->willReturn($namesByTypes);
        $handler->expects($this->once())
                ->method('fetchIconFileHashes')
                ->with($this->identicalTo($namesByTypes))
                ->willReturn($iconFileHashes);
        $handler->expects($this->once())
                ->method('fetchIcons')
                ->with($this->identicalTo($iconFileHashes))
                ->willReturn($clientIcons);
        $handler->expects($this->once())
                ->method('filterRequestedIcons')
                ->with($this->identicalTo($clientIcons), $this->identicalTo($namesByTypes))
                ->willReturn($filteredClientIcons);
        $handler->expects($this->once())
                ->method('hydrateContentToIcons')
                ->with($this->identicalTo($filteredClientIcons));
        $handler->expects($this->once())
                ->method('createResponse')
                ->with($this->identicalTo($filteredClientIcons))
                ->willReturn($response);

        $result = $this->invokeMethod($handler, 'handleRequest', $request);

        $this->assertSame($response, $result);
    }

    /**
     * Tests the fetchIconFileHashes method.
     * @throws ReflectionException
     * @covers ::fetchIconFileHashes
     */
    public function testFetchIconFileHashes(): void
    {
        $namesByTypes = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];
        $iconFileHashes = ['pqr', 'stu'];
        $allNamesByTypes = [
            'vwx' => ['yza', 'bcd'],
            'efg' => ['hij'],
        ];
        $allIconFileHashes = ['klm', 'nop'];

        $this->iconService->expects($this->exactly(2))
                          ->method('getIconFileHashesByTypesAndNames')
                          ->withConsecutive(
                              [$this->identicalTo($namesByTypes)],
                              [$this->identicalTo($allNamesByTypes)]
                          )
                          ->willReturnOnConsecutiveCalls(
                              $iconFileHashes,
                              $allIconFileHashes
                          );
        $this->iconService->expects($this->once())
                          ->method('getAllTypesAndNamesByHashes')
                          ->with($this->identicalTo($iconFileHashes))
                          ->willReturn($allNamesByTypes);

        $handler = new GenericIconHandler($this->iconService);
        $result = $this->invokeMethod($handler, 'fetchIconFileHashes', $namesByTypes);

        $this->assertSame($allIconFileHashes, $result);
    }

    /**
     * Tests the fetchIcons method.
     * @throws ReflectionException
     * @covers ::fetchIcons
     */
    public function testFetchIcons(): void
    {
        $iconFileHashes = ['abc', 'def'];

        /* @var IconData&MockObject $iconData1 */
        $iconData1 = $this->createMock(IconData::class);
        $iconData1->expects($this->once())
                  ->method('getHash')
                  ->willReturn('abc');

        /* @var IconData&MockObject $iconData2 */
        $iconData2 = $this->createMock(IconData::class);
        $iconData2->expects($this->once())
                  ->method('getHash')
                  ->willReturn('abc');

        /* @var IconData&MockObject $iconData3 */
        $iconData3 = $this->createMock(IconData::class);
        $iconData3->expects($this->once())
                  ->method('getHash')
                  ->willReturn('def');

        /* @var Entity&MockObject $entity1 */
        $entity1 = $this->createMock(Entity::class);
        /* @var Entity&MockObject $entity2 */
        $entity2 = $this->createMock(Entity::class);
        /* @var Entity&MockObject $entity3 */
        $entity3 = $this->createMock(Entity::class);

        /* @var ClientIcon&MockObject $clientIcon1 */
        $clientIcon1 = $this->createMock(ClientIcon::class);
        $clientIcon1->expects($this->exactly(2))
                    ->method('addEntity')
                    ->withConsecutive(
                        [$this->identicalTo($entity1)],
                        [$this->identicalTo($entity2)]
                    );

        /* @var ClientIcon&MockObject $clientIcon2 */
        $clientIcon2 = $this->createMock(ClientIcon::class);
        $clientIcon2->expects($this->once())
                    ->method('addEntity')
                    ->with($this->identicalTo($entity3));

        $expectedResult = [
            'abc' => $clientIcon1,
            'def' => $clientIcon2,
        ];

        $this->iconService->expects($this->once())
                          ->method('getIconDataByHashes')
                          ->with($this->identicalTo($iconFileHashes))
                          ->willReturn([$iconData1, $iconData2, $iconData3]);

        /* @var GenericIconHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericIconHandler::class)
                        ->setMethods(['createClientIcon', 'createEntityForIconData'])
                        ->setConstructorArgs([$this->iconService])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('createClientIcon')
                ->willReturnOnConsecutiveCalls(
                    $clientIcon1,
                    $clientIcon2
                );
        $handler->expects($this->exactly(3))
                ->method('createEntityForIconData')
                ->withConsecutive(
                    [$this->identicalTo($iconData1)],
                    [$this->identicalTo($iconData2)],
                    [$this->identicalTo($iconData3)]
                )
                ->willReturnOnConsecutiveCalls(
                    $entity1,
                    $entity2,
                    $entity3
                );

        $result = $this->invokeMethod($handler, 'fetchIcons', $iconFileHashes);

        $this->assertEquals($expectedResult, $result);
    }


    /**
     * Tests the createClientIcon method.
     * @throws ReflectionException
     * @covers ::createClientIcon
     */
    public function testCreateClientIcon(): void
    {
        $expectedResult = new ClientIcon();

        $handler = new GenericIconHandler($this->iconService);
        $result = $this->invokeMethod($handler, 'createClientIcon');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the createEntityForIconData method.
     * @throws ReflectionException
     * @covers ::createEntityForIconData
     */
    public function testCreateEntityForIconData(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = new Entity();
        $expectedResult->setType($type)
                       ->setName($name);

        /* @var IconData&MockObject $iconData */
        $iconData = $this->createMock(IconData::class);
        $iconData->expects($this->once())
                 ->method('getType')
                 ->willReturn($type);
        $iconData->expects($this->once())
                 ->method('getName')
                 ->willReturn($name);

        $handler = new GenericIconHandler($this->iconService);
        $result = $this->invokeMethod($handler, 'createEntityForIconData', $iconData);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the filterRequestedIcons method.
     * @throws ReflectionException
     * @covers ::filterRequestedIcons
     */
    public function testFilterRequestedIcons(): void
    {
        $namesByTypes = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];

        /* @var ClientIcon&MockObject $clientIcon1 */
        $clientIcon1 = $this->createMock(ClientIcon::class);
        /* @var ClientIcon&MockObject $clientIcon2 */
        $clientIcon2 = $this->createMock(ClientIcon::class);
        /* @var ClientIcon&MockObject $clientIcon3 */
        $clientIcon3 = $this->createMock(ClientIcon::class);

        $clientIcons = [
            'pqr' => $clientIcon1,
            'stu' => $clientIcon2,
            'vwx' => $clientIcon3,
        ];
        $expectedResult = [
            'pqr' => $clientIcon1,
            'vwx' => $clientIcon3,
        ];

        /* @var GenericIconHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericIconHandler::class)
                        ->setMethods(['wasIconRequested'])
                        ->setConstructorArgs([$this->iconService])
                        ->getMock();
        $handler->expects($this->exactly(3))
                ->method('wasIconRequested')
                ->withConsecutive(
                    [$this->identicalTo($clientIcon1)],
                    [$this->identicalTo($clientIcon2)],
                    [$this->identicalTo($clientIcon3)]
                )
                ->willReturnOnConsecutiveCalls(
                    true,
                    false,
                    true
                );

        $result = $this->invokeMethod($handler, 'filterRequestedIcons', $clientIcons, $namesByTypes);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the wasIconRequested method.
     * @throws ReflectionException
     * @covers ::wasIconRequested
     */
    public function testWasIconRequested(): void
    {
        $namesByTypes = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];
        
        /* @var Entity&MockObject $entity1 */
        $entity1 = $this->createMock(Entity::class);
        $entity1->expects($this->once())
                ->method('getType')
                ->willReturn('foo');
        $entity1->expects($this->once())
                ->method('getName')
                ->willReturn('def');
        
        /* @var Entity&MockObject $entity2 */
        $entity2 = $this->createMock(Entity::class);
        $entity2->expects($this->once())
                ->method('getType')
                ->willReturn('abc');
        $entity2->expects($this->once())
                ->method('getName')
                ->willReturn('def');

        /* @var ClientIcon&MockObject $clientIcon */
        $clientIcon = $this->createMock(ClientIcon::class);
        $clientIcon->expects($this->once())
                   ->method('getEntities')
                   ->willReturn([$entity1, $entity2]);

        $handler = new GenericIconHandler($this->iconService);
        $result = $this->invokeMethod($handler, 'wasIconRequested', $clientIcon, $namesByTypes);

        $this->assertTrue($result);
    }

    /**
     * Tests the wasIconRequested method with an actually unrequested icon.
     * @throws ReflectionException
     * @covers ::wasIconRequested
     */
    public function testWasIconRequestedWithUnrequestedIcon(): void
    {
        $namesByTypes = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno'],
        ];

        /* @var Entity&MockObject $entity1 */
        $entity1 = $this->createMock(Entity::class);
        $entity1->expects($this->once())
                ->method('getType')
                ->willReturn('foo');
        $entity1->expects($this->once())
                ->method('getName')
                ->willReturn('def');

        /* @var Entity&MockObject $entity2 */
        $entity2 = $this->createMock(Entity::class);
        $entity2->expects($this->once())
                ->method('getType')
                ->willReturn('abc');
        $entity2->expects($this->once())
                ->method('getName')
                ->willReturn('bar');

        /* @var ClientIcon&MockObject $clientIcon */
        $clientIcon = $this->createMock(ClientIcon::class);
        $clientIcon->expects($this->once())
                   ->method('getEntities')
                   ->willReturn([$entity1, $entity2]);

        $handler = new GenericIconHandler($this->iconService);
        $result = $this->invokeMethod($handler, 'wasIconRequested', $clientIcon, $namesByTypes);

        $this->assertFalse($result);
    }

    /**
     * Tests the hydrateContentToIcons method.
     * @throws ReflectionException
     * @covers ::hydrateContentToIcons
     */
    public function testHydrateContentToIcons(): void
    {
        $image1 = 'foo';
        $image2 = 'bar';

        /* @var ClientIcon&MockObject $clientIcon1 */
        $clientIcon1 = $this->createMock(ClientIcon::class);
        $clientIcon1->expects($this->once())
                    ->method('setContent')
                    ->with($this->identicalTo($image1));

        /* @var ClientIcon&MockObject $clientIcon2 */
        $clientIcon2 = $this->createMock(ClientIcon::class);
        $clientIcon2->expects($this->once())
                    ->method('setContent')
                    ->with($this->identicalTo($image2));

        $clientIcons = [
            'abc' => $clientIcon1,
            'def' => $clientIcon2,
        ];
        $iconFileHashes = ['abc', 'def'];

        /* @var IconFile&MockObject $iconFile1 */
        $iconFile1 = $this->createMock(IconFile::class);
        $iconFile1->expects($this->once())
                  ->method('getHash')
                  ->willReturn('abc');
        $iconFile1->expects($this->once())
                  ->method('getImage')
                  ->willReturn($image1);

        /* @var IconFile&MockObject $iconFile2 */
        $iconFile2 = $this->createMock(IconFile::class);
        $iconFile2->expects($this->once())
                  ->method('getHash')
                  ->willReturn('def');
        $iconFile2->expects($this->once())
                  ->method('getImage')
                  ->willReturn($image2);

        $iconFiles = [$iconFile1, $iconFile2];

        $this->iconService->expects($this->once())
                          ->method('getIconFilesByHashes')
                          ->with($this->equalTo($iconFileHashes))
                          ->willReturn($iconFiles);

        $handler = new GenericIconHandler($this->iconService);
        $this->invokeMethod($handler, 'hydrateContentToIcons', $clientIcons);
    }

    /**
     * Tests the createResponse method.
     * @throws ReflectionException
     * @covers ::createResponse
     */
    public function testCreateResponse(): void
    {
        $clientIcons = [
            $this->createMock(ClientIcon::class),
            $this->createMock(ClientIcon::class),
        ];

        $expectedResult = new GenericIconResponse();
        $expectedResult->setIcons($clientIcons);

        $handler = new GenericIconHandler($this->iconService);
        $result = $this->invokeMethod($handler, 'createResponse', $clientIcons);

        $this->assertEquals($expectedResult, $result);
    }
}
