<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Transfer\Entity;
use FactorioItemBrowser\Api\Client\Transfer\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Request\Generic\GenericIconRequest;
use FactorioItemBrowser\Api\Client\Response\Generic\GenericIconResponse;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Icon as DatabaseIcon;
use FactorioItemBrowser\Api\Database\Entity\IconImage;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericIconHandler;
use FactorioItemBrowser\Api\Server\Response\ClientResponse;
use FactorioItemBrowser\Api\Server\Service\IconService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
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

    /** @var IconService&MockObject */
    private IconService $iconService;

    protected function setUp(): void
    {
        $this->iconService = $this->createMock(IconService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return GenericIconHandler&MockObject
     */
    private function createInstance(array $mockedMethods = []): GenericIconHandler
    {
        return $this->getMockBuilder(GenericIconHandler::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->iconService,
                    ])
                    ->getMock();
    }

    public function testHandle(): void
    {
        $requestEntities = [
            $this->createMock(Entity::class),
            $this->createMock(Entity::class),
        ];
        $clientRequest = new GenericIconRequest();
        $clientRequest->combinationId = '2f4a45fa-a509-a9d1-aae6-ffcf984a7a76';
        $clientRequest->entities = $requestEntities;

        $namesByTypes = $this->createMock(NamesByTypes::class);
        $imageIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];
        $icons = [
            $this->createMock(ClientIcon::class),
            $this->createMock(ClientIcon::class),
        ];
        $filteredIcons = [
            $this->createMock(ClientIcon::class),
            $this->createMock(ClientIcon::class),
        ];

        $expectedPayload = new GenericIconResponse();
        $expectedPayload->icons = $filteredIcons;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getParsedBody')
                ->willReturn($clientRequest);

        $this->iconService->expects($this->once())
                          ->method('setCombinationId')
                          ->with($this->equalTo(Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76')));

        $instance = $this->createInstance([
            'extractTypesAndNames',
            'fetchImageIds',
            'fetchIcons',
            'filterRequestedIcons',
            'hydrateContentToIcons',
        ]);
        $instance->expects($this->once())
                 ->method('extractTypesAndNames')
                 ->with($this->identicalTo($requestEntities))
                 ->willReturn($namesByTypes);
        $instance->expects($this->once())
                 ->method('fetchImageIds')
                 ->with($this->identicalTo($namesByTypes))
                 ->willReturn($imageIds);
        $instance->expects($this->once())
                 ->method('fetchIcons')
                 ->with($this->identicalTo($imageIds))
                 ->willReturn($icons);
        $instance->expects($this->once())
                 ->method('filterRequestedIcons')
                 ->with($this->identicalTo($icons), $this->identicalTo($namesByTypes))
                 ->willReturn($filteredIcons);
        $instance->expects($this->once())
                 ->method('hydrateContentToIcons')
                 ->with($this->identicalTo($filteredIcons));

        $result = $instance->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $result);
        /* @var ClientResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    /**
     * @throws ReflectionException
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

        $namesByTypes = $this->createMock(NamesByTypes::class);
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

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchImageIds', $namesByTypes);

        $this->assertSame($allImageIds, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchIcons(): void
    {
        $id1 = Uuid::fromString('1a508039-afb2-4bc3-8c54-5e008dce5e12');
        $id2 = Uuid::fromString('2735b68e-766c-4da1-b12a-269ef9adb3ed');

        $imageIds = [$id1, $id2];

        $image1 = new IconImage();
        $image1->setId($id1);
        $image2 = new IconImage();
        $image2->setId($id2);

        $icon1 = new DatabaseIcon();
        $icon1->setType('abc')
              ->setName('def')
              ->setImage($image1);
        $icon2 = new DatabaseIcon();
        $icon2->setType('ghi')
              ->setName('jkl')
              ->setImage($image2);
        $icon3 = new DatabaseIcon();
        $icon3->setType('mno')
              ->setName('pqr')
              ->setImage($image1);

        $entity1 = new Entity();
        $entity1->type = 'abc';
        $entity1->name = 'def';
        $entity2 = new Entity();
        $entity2->type = 'ghi';
        $entity2->name = 'jkl';
        $entity3 = new Entity();
        $entity3->type = 'mno';
        $entity3->name = 'pqr';

        $expectedIcon1 = new ClientIcon();
        $expectedIcon1->entities = [$entity1, $entity3];
        $expectedIcon2 = new ClientIcon();
        $expectedIcon2->entities = [$entity2];

        $expectedResult = [
            '1a508039-afb2-4bc3-8c54-5e008dce5e12' => $expectedIcon1,
            '2735b68e-766c-4da1-b12a-269ef9adb3ed' => $expectedIcon2,
        ];

        $this->iconService->expects($this->once())
                          ->method('getIconsByImageIds')
                          ->with($this->identicalTo($imageIds))
                          ->willReturn([$icon1, $icon2, $icon3]);

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'fetchIcons', $imageIds);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testFilterRequestedIcons(): void
    {
        $entity1a = new Entity();
        $entity1a->type = 'abc';
        $entity1a->name = 'def';
        $entity1b = new Entity();
        $entity1b->type = 'ghi';
        $entity1b->name = 'jkl';
        $entity2 = new Entity();
        $entity2->type = 'mno';
        $entity2->name = 'pqr';
        $entity3 = new Entity();
        $entity3->type = 'stu';
        $entity3->name = 'vwx';

        $icon1 = new ClientIcon();
        $icon1->entities = [$entity1a, $entity1b];
        $icon2 = new ClientIcon();
        $icon2->entities = [$entity2];
        $icon3 = new ClientIcon();
        $icon3->entities = [$entity3];

        $namesByTypes = new NamesByTypes();
        $namesByTypes->addName('ghi', 'jkl')
                     ->addName('stu', 'vwx')
                     ->addName('mno', 'yza');

        $icons = [
            'foo' => $icon1,
            'bar' => $icon2,
            'baz' => $icon3,
        ];
        $expectedResult = [
            'foo' => $icon1,
            'baz' => $icon3,
        ];

        $instance = $this->createInstance();
        $result = $this->invokeMethod($instance, 'filterRequestedIcons', $icons, $namesByTypes);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testHydrateContentToIcons(): void
    {
        $id1 = Uuid::fromString('1a508039-afb2-4bc3-8c54-5e008dce5e12');
        $id2 = Uuid::fromString('2735b68e-766c-4da1-b12a-269ef9adb3ed');
        $iconIds = [$id1, $id2];

        $iconImage1 = new IconImage();
        $iconImage1->setId($id1)
                   ->setContents('foo')
                   ->setSize(42);
        $iconImage2 = new IconImage();
        $iconImage2->setId($id2)
                   ->setContents('bar')
                   ->setSize(1337);
        $iconImages = [$iconImage1, $iconImage2];

        $entity1 = new Entity();
        $entity1->type = 'abc';
        $entity1->name = 'def';
        $entity2 = new Entity();
        $entity2->type = 'ghi';
        $entity2->name = 'jkl';

        $icon1 = new ClientIcon();
        $icon1->entities = [$entity1];
        $icon2 = new ClientIcon();
        $icon2->entities = [$entity2];
        $icons = [
            '1a508039-afb2-4bc3-8c54-5e008dce5e12' => $icon1,
            '2735b68e-766c-4da1-b12a-269ef9adb3ed' => $icon2,
        ];

        $expectedIcon1 = new ClientIcon();
        $expectedIcon1->entities = [$entity1];
        $expectedIcon1->content = 'foo';
        $expectedIcon1->size = 42;
        $expectedIcon2 = new ClientIcon();
        $expectedIcon2->entities = [$entity2];
        $expectedIcon2->content = 'bar';
        $expectedIcon2->size = 1337;
        $expectedIcons = [
            '1a508039-afb2-4bc3-8c54-5e008dce5e12' => $expectedIcon1,
            '2735b68e-766c-4da1-b12a-269ef9adb3ed' => $expectedIcon2,
        ];

        $this->iconService->expects($this->once())
                          ->method('getImagesByIds')
                          ->with($this->equalTo($iconIds))
                          ->willReturn($iconImages);

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'hydrateContentToIcons', $icons);

        $this->assertEquals($expectedIcons, $icons);
    }
}
