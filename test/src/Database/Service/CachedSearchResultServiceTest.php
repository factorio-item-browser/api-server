<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Database\Service;

use BluePsyduck\Common\Test\ReflectionTrait;
use DateTime;
use Doctrine\ORM\EntityManager;
use FactorioItemBrowser\Api\Database\Entity\CachedSearchResult;
use FactorioItemBrowser\Api\Database\Repository\CachedSearchResultRepository;
use FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService;
use FactorioItemBrowser\Api\Server\Database\Service\ModService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the CachedSearchResultService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Database\Service\CachedSearchResultService
 */
class CachedSearchResultServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @covers ::__construct
     * @covers ::initializeRepositories
     */
    public function testConstruct()
    {
        /* @var CachedSearchResultRepository $cachedSearchResultRepository */
        $cachedSearchResultRepository = $this->createMock(CachedSearchResultRepository::class);
        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);
        /* @var TranslationService $translationService */
        $translationService = $this->createMock(TranslationService::class);

        /* @var EntityManager|MockObject $entityManager */
        $entityManager = $this->getMockBuilder(EntityManager::class)
                              ->setMethods(['getRepository'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $entityManager->expects($this->once())
                      ->method('getRepository')
                      ->with(CachedSearchResult::class)
                      ->willReturn($cachedSearchResultRepository);

        $service = new CachedSearchResultService($entityManager, $modService, $translationService);
        $this->assertSame($entityManager, $this->extractProperty($service, 'entityManager'));
        $this->assertSame($modService, $this->extractProperty($service, 'modService'));
        $this->assertSame($translationService, $this->extractProperty($service, 'translationService'));
        $this->assertSame(
            $cachedSearchResultRepository,
            $this->extractProperty($service, 'cachedSearchResultRepository')
        );
    }

    /**
     * Tests the cleanup method.
     * @covers ::cleanup
     */
    public function testCleanup()
    {
        $maxAge = new DateTime('2038-01-19 03:14:07');
        /* @var ModService $modService */
        $modService = $this->createMock(ModService::class);
        /* @var TranslationService $translationService */
        $translationService = $this->createMock(TranslationService::class);

        /* @var CachedSearchResultRepository|MockObject $cachedSearchResultRepository */
        $cachedSearchResultRepository = $this->getMockBuilder(CachedSearchResultRepository::class)
                                             ->setMethods(['cleanup'])
                                             ->disableOriginalConstructor()
                                             ->getMock();
        $cachedSearchResultRepository->expects($this->once())
                                     ->method('cleanup')
                                     ->with($maxAge);

        /* @var EntityManager|MockObject $entityManager */
        $entityManager = $this->getMockBuilder(EntityManager::class)
                              ->setMethods(['getRepository'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $entityManager->expects($this->once())
                      ->method('getRepository')
                      ->with(CachedSearchResult::class)
                      ->willReturn($cachedSearchResultRepository);

        /* @var CachedSearchResultService|MockObject $service */
        $service = $this->getMockBuilder(CachedSearchResultService::class)
                        ->setMethods(['getMaxAge'])
                        ->setConstructorArgs([$entityManager, $modService, $translationService])
                        ->getMock();
        $service->expects($this->once())
                ->method('getMaxAge')
                ->willReturn($maxAge);

        $this->assertSame($service, $service->cleanup());
    }
}
