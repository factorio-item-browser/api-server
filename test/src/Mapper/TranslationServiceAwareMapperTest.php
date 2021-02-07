<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Transfer\GenericEntity;
use FactorioItemBrowser\Api\Server\Mapper\TranslationServiceAwareMapper;
use FactorioItemBrowser\Api\Server\Service\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the TranslationServiceAwareMapper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Mapper\TranslationServiceAwareMapper
 */
class TranslationServiceAwareMapperTest extends TestCase
{
    use ReflectionTrait;

    /** @var TranslationService&MockObject */
    private TranslationService $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return TranslationServiceAwareMapper&MockObject
     */
    private function createInstance(array $mockedMethods = []): TranslationServiceAwareMapper
    {
        return $this->getMockBuilder(TranslationServiceAwareMapper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->translationService,
                    ])
                    ->getMockForAbstractClass();
    }

    /**
     * @throws ReflectionException
     */
    public function testAddToTranslationService(): void
    {
        $entity = $this->createMock(GenericEntity::class);

        $this->translationService->expects($this->once())
                                 ->method('addEntity')
                                 ->with($this->identicalTo($entity));

        $instance = $this->createInstance();
        $this->invokeMethod($instance, 'addToTranslationService', $entity);
    }
}
