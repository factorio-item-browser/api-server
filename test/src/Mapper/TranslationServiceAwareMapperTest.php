<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Mapper;

use BluePsyduck\Common\Test\ReflectionTrait;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
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
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Mapper\TranslationServiceAwareMapper
 */
class TranslationServiceAwareMapperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked translation service.
     * @var TranslationService&MockObject
     */
    protected $translationService;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translationService = $this->createMock(TranslationService::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        /* @var TranslationServiceAwareMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(TranslationServiceAwareMapper::class)
                       ->setConstructorArgs([$this->translationService])
                       ->getMockForAbstractClass();

        $this->assertSame($this->translationService, $this->extractProperty($mapper, 'translationService'));
    }

    /**
     * Tests the addToTranslationService method.
     * @throws ReflectionException
     * @covers ::addToTranslationService
     */
    public function testAddToTranslationService(): void
    {
        /* @var GenericEntity&MockObject $entity */
        $entity = $this->createMock(GenericEntity::class);

        $this->translationService->expects($this->once())
                                 ->method('addEntity')
                                 ->with($this->identicalTo($entity));

        /* @var TranslationServiceAwareMapper&MockObject $mapper */
        $mapper = $this->getMockBuilder(TranslationServiceAwareMapper::class)
                       ->setConstructorArgs([$this->translationService])
                       ->getMockForAbstractClass();

        $this->invokeMethod($mapper, 'addToTranslationService', $entity);
    }
}
