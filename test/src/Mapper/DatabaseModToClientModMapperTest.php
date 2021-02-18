<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use FactorioItemBrowser\Api\Client\Transfer\Mod as ClientMod;
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
 * @covers \FactorioItemBrowser\Api\Server\Mapper\DatabaseModToClientModMapper
 */
class DatabaseModToClientModMapperTest extends TestCase
{
    /** @var TranslationService&MockObject */
    private TranslationService $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return DatabaseModToClientModMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): DatabaseModToClientModMapper
    {
        return $this->getMockBuilder(DatabaseModToClientModMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->translationService,
                    ])
                    ->getMock();
    }

    public function testSupports(): void
    {
        $instance = $this->createInstance();

        $this->assertSame(DatabaseMod::class, $instance->getSupportedSourceClass());
        $this->assertSame(ClientMod::class, $instance->getSupportedDestinationClass());
    }

    public function testMap(): void
    {
        $source = new DatabaseMod();
        $source->setName('abc')
               ->setAuthor('def')
               ->setVersion('ghi');

        $expectedDestination = new ClientMod();
        $expectedDestination->name = 'abc';
        $expectedDestination->author = 'def';
        $expectedDestination->version = 'ghi';

        $destination = new ClientMod();

        $instance = $this->createInstance(['addToTranslationService']);
        $instance->expects($this->once())
                 ->method('addToTranslationService')
                 ->with($this->equalTo($expectedDestination));

        $instance->map($source, $destination);

        $this->assertEquals($expectedDestination, $destination);
    }
}
