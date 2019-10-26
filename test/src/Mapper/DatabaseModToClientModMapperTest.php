<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Entity\Mod as ClientMod;
use FactorioItemBrowser\Api\Database\Entity\Mod as DatabaseMod;
use FactorioItemBrowser\Api\Server\Mapper\DatabaseModToClientModMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the DatabaseModToClientModMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\DatabaseModToClientModMapper
 */
class DatabaseModToClientModMapperTest extends TestCase
{
    /**
     * The mocked translation service.
     * @var TranslationService&MockObject
     */
    protected $translationService;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * Tests the getSupportedSourceClass method.
     * @covers ::getSupportedSourceClass
     */
    public function testGetSupportedSourceClass(): void
    {
        $expectedResult = DatabaseMod::class;

        $mapper = new DatabaseModToClientModMapper($this->translationService);
        $result = $mapper->getSupportedSourceClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the getSupportedDestinationClass method.
     * @covers ::getSupportedDestinationClass
     */
    public function testGetSupportedDestinationClass(): void
    {
        $expectedResult = ClientMod::class;

        $mapper = new DatabaseModToClientModMapper($this->translationService);
        $result = $mapper->getSupportedDestinationClass();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the map method.
     * @covers ::map
     */
    public function testMap(): void
    {
        /* @var DatabaseMod&MockObject $databaseMod */
        $databaseMod = $this->createMock(DatabaseMod::class);
        $databaseMod->expects($this->once())
                    ->method('getName')
                    ->willReturn('abc');
        $databaseMod->expects($this->once())
                    ->method('getAuthor')
                    ->willReturn('def');
        $databaseMod->expects($this->once())
                    ->method('getVersion')
                    ->willReturn('ghi');

        /* @var ClientMod&MockObject $clientMod */
        $clientMod = $this->createMock(ClientMod::class);
        $clientMod->expects($this->once())
                  ->method('setName')
                  ->with($this->identicalTo('abc'))
                  ->willReturnSelf();
        $clientMod->expects($this->once())
                  ->method('setAuthor')
                  ->with($this->identicalTo('def'))
                  ->willReturnSelf();
        $clientMod->expects($this->once())
                  ->method('setVersion')
                  ->with($this->identicalTo('ghi'))
                  ->willReturnSelf();

        /* @var DatabaseModToClientModMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(DatabaseModToClientModMapper::class)
                       ->onlyMethods(['addToTranslationService'])
                       ->setConstructorArgs([$this->translationService])
                       ->getMock();
        $mapper->expects($this->once())
               ->method('addToTranslationService')
               ->with($this->identicalTo($clientMod));

        $mapper->map($databaseMod, $clientMod);
    }
}
