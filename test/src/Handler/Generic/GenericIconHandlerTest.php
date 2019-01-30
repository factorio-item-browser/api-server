<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;
use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\Icon as ClientIcon;
use FactorioItemBrowser\Api\Client\Entity\IconEntity as ClientIconEntity;
use FactorioItemBrowser\Api\Database\Entity\Icon as DatabaseIcon;
use FactorioItemBrowser\Api\Database\Entity\IconFile as DatabaseIconFile;
use FactorioItemBrowser\Api\Server\Database\Service\IconService;
use FactorioItemBrowser\Api\Server\Handler\Generic\GenericIconHandler;
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
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var IconService&MockObject $iconService */
        $iconService = $this->createMock(IconService::class);

        $handler = new GenericIconHandler($iconService);

        $this->assertSame($iconService, $this->extractProperty($handler, 'iconService'));
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
        ];
        $iconFileHashes = [
            'ab12cd34',
            '12ab34cd',
        ];

        /* @var ClientIcon&MockObject $clientIcon */
        $clientIcon = $this->createMock(ClientIcon::class);
        /* @var DataContainer&MockObject $requestData */
        $requestData = $this->createMock(DataContainer::class);

        $clientIcons = [
            'jkl' => $this->createMock(ClientIcon::class),
            'mno' => $this->createMock(ClientIcon::class),
        ];
        $filteredClientIcons = [
            'pqr' => $clientIcon,
        ];
        $expectedResult = [
            'icons' => [$clientIcon],
        ];

        /* @var GenericIconHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericIconHandler::class)
                        ->setMethods([
                            'getEntityNamesByType',
                            'getIconFileHashesByTypesAndNames',
                            'prepareClientIcons',
                            'fetchEntitiesToIcons',
                            'filterRequestedIcons',
                            'hydrateContentToIcons',
                        ])
                        ->disableOriginalConstructor()
                        ->getMock();
        $handler->expects($this->once())
                ->method('getEntityNamesByType')
                ->with($this->identicalTo($requestData))
                ->willReturn($namesByTypes);
        $handler->expects($this->once())
                ->method('getIconFileHashesByTypesAndNames')
                ->with($this->identicalTo($namesByTypes))
                ->willReturn($iconFileHashes);
        $handler->expects($this->once())
                ->method('prepareClientIcons')
                ->with($this->identicalTo($iconFileHashes))
                ->willReturn($clientIcons);
        $handler->expects($this->once())
                ->method('fetchEntitiesToIcons')
                ->with($this->identicalTo($clientIcons));
        $handler->expects($this->once())
                ->method('filterRequestedIcons')
                ->with($this->identicalTo($clientIcons))
                ->willReturn($filteredClientIcons);
        $handler->expects($this->once())
                ->method('hydrateContentToIcons')
                ->with($filteredClientIcons);

        $result = $this->invokeMethod($handler, 'handleRequest', $requestData);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getIconFileHashesByTypesAndNames method.
     * @throws ReflectionException
     * @covers ::getIconFileHashesByTypesAndNames
     */
    public function testGetIconFileHashesByTypesAndNames(): void
    {
        $namesByTypes = [
            'abc' => ['def', 'ghi'],
        ];
        $iconFileHashes = ['ab12cd34'];
        $allNamesByTypes = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno', 'pqr'],
        ];
        $allIconFileHashes = ['ab12cd34', '12ab34cd'];

        /* @var IconService&MockObject $iconService */
        $iconService = $this->createMock(IconService::class);
        $iconService->expects($this->exactly(2))
                    ->method('getIconFileHashesByTypesAndNames')
                    ->withConsecutive(
                        [$this->identicalTo($namesByTypes)],
                        [$this->identicalTo($allNamesByTypes)]
                    )
                    ->willReturnOnConsecutiveCalls(
                        $iconFileHashes,
                        $allIconFileHashes
                    );
        $iconService->expects($this->once())
                    ->method('getAllTypesAndNamesByHashes')
                    ->with($this->identicalTo($iconFileHashes))
                    ->willReturn($allNamesByTypes);

        $handler = new GenericIconHandler($iconService);
        $result = $this->invokeMethod($handler, 'getIconFileHashesByTypesAndNames', $namesByTypes);

        $this->assertSame($allIconFileHashes, $result);
    }

    /**
     * Tests the prepareClientIcons method.
     * @throws ReflectionException
     * @covers ::prepareClientIcons
     */
    public function testPrepareClientIcons(): void
    {
        $iconFileHashes = [
            'ab12cd34',
            '12ab34cd',
        ];
        $expectedResult = [
            'ab12cd34' => new ClientIcon(),
            '12ab34cd' => new ClientIcon(),
        ];

        /* @var IconService&MockObject $iconService */
        $iconService = $this->createMock(IconService::class);

        $handler = new GenericIconHandler($iconService);
        $result = $this->invokeMethod($handler, 'prepareClientIcons', $iconFileHashes);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the fetchEntitiesToIcons method.
     * @throws ReflectionException
     * @covers ::fetchEntitiesToIcons
     */
    public function testFetchEntitiesToIcons(): void
    {
        /* @var ClientIconEntity&MockObject $clientIconEntity */
        $clientIconEntity = $this->createMock(ClientIconEntity::class);

        /* @var ClientIcon&MockObject $clientIcon1 */
        $clientIcon1 = $this->createMock(ClientIcon::class);
        $clientIcon1->expects($this->once())
                    ->method('addEntity')
                    ->with($this->identicalTo($clientIconEntity));

        /* @var ClientIcon&MockObject $clientIcon2 */
        $clientIcon2 = $this->createMock(ClientIcon::class);
        $clientIcon2->expects($this->never())
                    ->method('addEntity');

        $clientIcons = [
            'ab12cd34' => $clientIcon1,
            '12ab34cd' => $clientIcon2,
        ];
        $iconFileHashes = ['ab12cd34', '12ab34cd'];

        /* @var DatabaseIcon&MockObject $databaseIcon1 */
        $databaseIcon1 = $this->createMock(DatabaseIcon::class);
        $databaseIcon1->expects($this->once())
                      ->method('getFile')
                      ->willReturn(new DatabaseIconFile('ab12cd34'));
        $databaseIcon1->expects($this->once())
                      ->method('getType')
                      ->willReturn('abc');
        $databaseIcon1->expects($this->once())
                      ->method('getName')
                      ->willReturn('def');

        /* @var DatabaseIcon&MockObject $databaseIcon2 */
        $databaseIcon2 = $this->createMock(DatabaseIcon::class);
        $databaseIcon2->expects($this->once())
                      ->method('getFile')
                      ->willReturn(new DatabaseIconFile('cd34ef56'));

        /* @var IconService&MockObject $iconService */
        $iconService = $this->createMock(IconService::class);
        $iconService->expects($this->once())
                    ->method('getIconsByHashes')
                    ->with($this->identicalTo($iconFileHashes))
                    ->willReturn([$databaseIcon1, $databaseIcon2]);

        /* @var GenericIconHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericIconHandler::class)
                        ->setMethods(['createClientIconEntity'])
                        ->setConstructorArgs([$iconService])
                        ->getMock();
        $handler->expects($this->once())
                ->method('createClientIconEntity')
                ->with($this->identicalTo('abc'), $this->identicalTo('def'))
                ->willReturn($clientIconEntity);

        $this->invokeMethod($handler, 'fetchEntitiesToIcons', $clientIcons);
    }

    /**
     * Tests the createClientIconEntity method.
     * @throws ReflectionException
     * @covers ::createClientIconEntity
     */
    public function testCreateClientIconEntity(): void
    {
        $type = 'abc';
        $name = 'def';
        $expectedResult = new ClientIconEntity();
        $expectedResult->setType($type)
                       ->setName($name);

        /* @var IconService&MockObject $iconService */
        $iconService = $this->createMock(IconService::class);

        $handler = new GenericIconHandler($iconService);
        $result = $this->invokeMethod($handler, 'createClientIconEntity', $type, $name);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the filterRequestedIcons method.
     * @covers ::filterRequestedIcons
     * @throws ReflectionException
     */
    public function testFilterRequestedIcons(): void
    {
        $namesByTypes = [
            'abc' => ['def', 'ghi'],
        ];

        /* @var ClientIcon&MockObject $clientIcon1 */
        $clientIcon1 = $this->createMock(ClientIcon::class);
        /* @var ClientIcon&MockObject $clientIcon2 */
        $clientIcon2 = $this->createMock(ClientIcon::class);

        $clientIcons = [
            'ab12cd34' => $clientIcon1,
            '12ab34cd' => $clientIcon2,
        ];
        $expectedResult = [
            'ab12cd34' => $clientIcon1,
        ];

        /* @var GenericIconHandler&MockObject $handler */
        $handler = $this->getMockBuilder(GenericIconHandler::class)
                        ->setMethods(['wasIconRequested'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $handler->expects($this->exactly(2))
                ->method('wasIconRequested')
                ->withConsecutive(
                    [$this->identicalTo($clientIcon1), $namesByTypes],
                    [$this->identicalTo($clientIcon2), $namesByTypes]
                )
                ->willReturnOnConsecutiveCalls(
                    true,
                    false
                );

        $result = $this->invokeMethod($handler, 'filterRequestedIcons', $clientIcons, $namesByTypes);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the wasIconRequested test.
     * @return array
     */
    public function provideWasIconRequested(): array
    {
        $entity1 = new ClientIconEntity();
        $entity1->setType('abc')
                ->setName('def');
        $entity2 = new ClientIconEntity();
        $entity2->setType('ghi')
                ->setName('jkl');

        $icon = new ClientIcon();
        $icon->addEntity($entity1)
              ->addEntity($entity2);

        return [
            [$icon, ['abc' => ['def']], true],
            [$icon, ['ghi' => ['jkl']], true],
            [$icon, ['abc' => ['foo']], false],
            [$icon, ['foo' => ['bar']], false],
        ];
    }

    /**
     * Tests the wasIconRequested method.
     * @covers ::wasIconRequested
     * @param ClientIcon $clientIcon
     * @param array|string[][] $namesByTypes
     * @param bool $expectedResult
     * @throws ReflectionException
     * @dataProvider provideWasIconRequested
     */
    public function testWasIconRequested(ClientIcon $clientIcon, array $namesByTypes, bool $expectedResult): void
    {
        /* @var IconService&MockObject $iconService */
        $iconService = $this->createMock(IconService::class);

        $handler = new GenericIconHandler($iconService);
        $result = $this->invokeMethod($handler, 'wasIconRequested', $clientIcon, $namesByTypes);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the hydrateContentToIcons method.
     * @throws ReflectionException
     * @covers ::hydrateContentToIcons
     */
    public function testHydrateContentToIcons(): void
    {
        $iconFile1 = new DatabaseIconFile('ab12cd34');
        $iconFile1->setImage('abc');
        $iconFile2 = new DatabaseIconFile('cd34ef56');
        $iconFile2->setImage('def');

        /* @var ClientIcon&MockObject $clientIcon1 */
        $clientIcon1 = $this->createMock(ClientIcon::class);
        $clientIcon1->expects($this->once())
                    ->method('setContent')
                    ->with(base64_encode('abc'));
        /* @var ClientIcon&MockObject $clientIcon2 */
        $clientIcon2 = $this->createMock(ClientIcon::class);
        $clientIcon2->expects($this->never())
                    ->method('setContent');

        $clientIcons = [
            'ab12cd34' => $clientIcon1,
            '12ab34cd' => $clientIcon2,
        ];
        $iconFileHashes = ['ab12cd34', '12ab34cd'];

        /* @var IconService&MockObject $iconService */
        $iconService = $this->createMock(IconService::class);
        $iconService->expects($this->once())
                    ->method('getIconFilesByHashes')
                    ->with($iconFileHashes)
                    ->willReturn([$iconFile1, $iconFile2]);

        $handler = new GenericIconHandler($iconService);
        $this->invokeMethod($handler, 'hydrateContentToIcons', $clientIcons);
    }
}
