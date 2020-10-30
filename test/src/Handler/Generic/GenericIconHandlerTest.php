<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Entity;
use FactorioItemBrowser\Api\Client\Entity\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Icon as DatabaseIcon;
use FactorioItemBrowser\Api\Database\Entity\IconImage;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericIconHandler;
use FactorioItemBrowser\Api\Server\Service\IconService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
        $imageIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];

        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
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
                        ->onlyMethods([
                            'getAuthorizationToken',
                            'extractTypesAndNames',
                            'fetchImageIds',
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
                ->method('fetchImageIds')
                ->with($this->identicalTo($namesByTypes))
                ->willReturn($imageIds);
        $handler->expects($this->once())
                ->method('fetchIcons')
                ->with($this->identicalTo($imageIds))
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
     * Tests the fetchImageIds method.
     * @throws ReflectionException
     * @covers ::fetchImageIds
     */
    public function testFetchImageIds(): void
    {
        $imageIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];
        $allImageIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];

        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        /* @var NamesByTypes&MockObject $allNamesByTypes */
        $allNamesByTypes = $this->createMock(NamesByTypes::class);

        $this->iconService->expects($this->exactly(2))
                          ->method('getImageIdsByTypesAndNames')
                          ->withConsecutive(
                              [$this->identicalTo($namesByTypes)],
                              [$this->identicalTo($allNamesByTypes)]
                          )
                          ->willReturnOnConsecutiveCalls(
                              $imageIds,
                              $allImageIds
                          );
        $this->iconService->expects($this->once())
                          ->method('getTypesAndNamesByImageIds')
                          ->with($this->identicalTo($imageIds))
                          ->willReturn($allNamesByTypes);

        $handler = new GenericIconHandler($this->iconService);
        $result = $this->invokeMethod($handler, 'fetchImageIds', $namesByTypes);

        $this->assertSame($allImageIds, $result);
    }

    /**
     * Tests the fetchIcons method.
     * @throws ReflectionException
     * @covers ::fetchIcons
     */
    public function testFetchIcons(): void
    {
        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');

        $imageIds = [$id1, $id2];

        /* @var IconImage&MockObject $image1 */
        $image1 = $this->createMock(IconImage::class);
        $image1->expects($this->any())
               ->method('getId')
               ->willReturn($id1);

        /* @var IconImage&MockObject $image2 */
        $image2 = $this->createMock(IconImage::class);
        $image2->expects($this->any())
               ->method('getId')
               ->willReturn($id2);

        /* @var DatabaseIcon&MockObject $icon1 */
        $icon1 = $this->createMock(DatabaseIcon::class);
        $icon1->expects($this->any())
              ->method('getImage')
              ->willReturn($image1);

        /* @var DatabaseIcon&MockObject $icon2 */
        $icon2 = $this->createMock(DatabaseIcon::class);
        $icon2->expects($this->any())
              ->method('getImage')
              ->willReturn($image1);

        /* @var DatabaseIcon&MockObject $icon3 */
        $icon3 = $this->createMock(DatabaseIcon::class);
        $icon3->expects($this->any())
              ->method('getImage')
              ->willReturn($image2);

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
            $id1->toString() => $clientIcon1,
            $id2->toString() => $clientIcon2,
        ];

        $this->iconService->expects($this->once())
                          ->method('getIconsByImageIds')
                          ->with($this->identicalTo($imageIds))
                          ->willReturn([$icon1, $icon2, $icon3]);

        /* @var GenericIconHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericIconHandler::class)
                        ->onlyMethods(['createClientIcon', 'createEntityForIcon'])
                        ->setConstructorArgs([$this->iconService])
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('createClientIcon')
                ->willReturnOnConsecutiveCalls(
                    $clientIcon1,
                    $clientIcon2
                );
        $handler->expects($this->exactly(3))
                ->method('createEntityForIcon')
                ->withConsecutive(
                    [$this->identicalTo($icon1)],
                    [$this->identicalTo($icon2)],
                    [$this->identicalTo($icon3)]
                )
                ->willReturnOnConsecutiveCalls(
                    $entity1,
                    $entity2,
                    $entity3
                );

        $result = $this->invokeMethod($handler, 'fetchIcons', $imageIds);

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
     * Tests the createEntityForIcon method.
     * @throws ReflectionException
     * @covers ::createEntityForIcon
     */
    public function testCreateEntityForIcon(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = new Entity();
        $expectedResult->setType($type)
                       ->setName($name);

        /* @var DatabaseIcon&MockObject $icon */
        $icon = $this->createMock(DatabaseIcon::class);
        $icon->expects($this->once())
             ->method('getType')
             ->willReturn($type);
        $icon->expects($this->once())
             ->method('getName')
             ->willReturn($name);

        $handler = new GenericIconHandler($this->iconService);
        $result = $this->invokeMethod($handler, 'createEntityForIcon', $icon);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the filterRequestedIcons method.
     * @throws ReflectionException
     * @covers ::filterRequestedIcons
     */
    public function testFilterRequestedIcons(): void
    {
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
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
                        ->onlyMethods(['wasIconRequested'])
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
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        $namesByTypes->expects($this->exactly(2))
                     ->method('hasName')
                     ->withConsecutive(
                         [$this->identicalTo('abc'), $this->identicalTo('def')],
                         [$this->identicalTo('ghi'), $this->identicalTo('jkl')]
                     )
                     ->willReturnOnConsecutiveCalls(
                         false,
                         true
                     );

        /* @var Entity&MockObject $entity1 */
        $entity1 = $this->createMock(Entity::class);
        $entity1->expects($this->once())
                ->method('getType')
                ->willReturn('abc');
        $entity1->expects($this->once())
                ->method('getName')
                ->willReturn('def');

        /* @var Entity&MockObject $entity2 */
        $entity2 = $this->createMock(Entity::class);
        $entity2->expects($this->once())
                ->method('getType')
                ->willReturn('ghi');
        $entity2->expects($this->once())
                ->method('getName')
                ->willReturn('jkl');

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
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);
        $namesByTypes->expects($this->exactly(2))
                     ->method('hasName')
                     ->withConsecutive(
                         [$this->identicalTo('abc'), $this->identicalTo('def')],
                         [$this->identicalTo('ghi'), $this->identicalTo('jkl')]
                     )
                     ->willReturnOnConsecutiveCalls(
                         false,
                         false
                     );

        /* @var Entity&MockObject $entity1 */
        $entity1 = $this->createMock(Entity::class);
        $entity1->expects($this->once())
                ->method('getType')
                ->willReturn('abc');
        $entity1->expects($this->once())
                ->method('getName')
                ->willReturn('def');

        /* @var Entity&MockObject $entity2 */
        $entity2 = $this->createMock(Entity::class);
        $entity2->expects($this->once())
                ->method('getType')
                ->willReturn('ghi');
        $entity2->expects($this->once())
                ->method('getName')
                ->willReturn('jkl');

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
        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');
        $imageContents1 = 'foo';
        $imageContents2 = 'bar';
        $size1 = 42;
        $size2 = 1337;

        $expectedImageIds = [$id1, $id2];

        /* @var ClientIcon&MockObject $clientIcon1 */
        $clientIcon1 = $this->createMock(ClientIcon::class);
        $clientIcon1->expects($this->once())
                    ->method('setContent')
                    ->with($this->identicalTo($imageContents1))
                    ->willReturnSelf();
        $clientIcon1->expects($this->once())
                    ->method('setSize')
                    ->with($this->identicalTo($size1))
                    ->willReturnSelf();

        /* @var ClientIcon&MockObject $clientIcon2 */
        $clientIcon2 = $this->createMock(ClientIcon::class);
        $clientIcon2->expects($this->once())
                    ->method('setContent')
                    ->with($this->identicalTo($imageContents2))
                    ->willReturnSelf();
        $clientIcon2->expects($this->once())
                    ->method('setSize')
                    ->with($this->identicalTo($size2))
                    ->willReturnSelf();

        $clientIcons = [
            $id1->toString() => $clientIcon1,
            $id2->toString() => $clientIcon2,
        ];

        /* @var IconImage&MockObject $image1 */
        $image1 = $this->createMock(IconImage::class);
        $image1->expects($this->once())
               ->method('getId')
               ->willReturn($id1);
        $image1->expects($this->once())
               ->method('getContents')
               ->willReturn($imageContents1);
        $image1->expects($this->once())
               ->method('getSize')
               ->willReturn($size1);

        /* @var IconImage&MockObject $image2 */
        $image2 = $this->createMock(IconImage::class);
        $image2->expects($this->once())
               ->method('getId')
               ->willReturn($id2);
        $image2->expects($this->once())
               ->method('getContents')
               ->willReturn($imageContents2);
        $image2->expects($this->once())
               ->method('getSize')
               ->willReturn($size2);

        $images = [$image1, $image2];

        $this->iconService->expects($this->once())
                          ->method('getImagesByIds')
                          ->with($this->equalTo($expectedImageIds))
                          ->willReturn($images);

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
