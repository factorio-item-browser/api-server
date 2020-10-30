<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Icon;
use FactorioItemBrowser\Api\Database\Entity\IconImage;
use FactorioItemBrowser\Api\Database\Repository\IconImageRepository;
use FactorioItemBrowser\Api\Database\Repository\IconRepository;
use FactorioItemBrowser\Api\Server\Entity\AuthorizationToken;
use FactorioItemBrowser\Api\Server\Service\IconService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the IconService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\Api\Server\Service\IconService
 */
class IconServiceTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked icon image repository.
     * @var IconImageRepository&MockObject
     */
    protected $iconImageRepository;

    /**
     * The mocked icon repository.
     * @var IconRepository&MockObject
     */
    protected $iconRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->iconImageRepository = $this->createMock(IconImageRepository::class);
        $this->iconRepository = $this->createMock(IconRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $service = new IconService($this->iconImageRepository, $this->iconRepository);

        $this->assertSame($this->iconImageRepository, $this->extractProperty($service, 'iconImageRepository'));
        $this->assertSame($this->iconRepository, $this->extractProperty($service, 'iconRepository'));
    }

    /**
     * Tests the injectAuthorizationToken method.
     * @throws ReflectionException
     * @covers ::injectAuthorizationToken
     */
    public function testInjectAuthorizationToken(): void
    {
        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);

        /* @var AuthorizationToken&MockObject $authorizationToken */
        $authorizationToken = $this->createMock(AuthorizationToken::class);
        $authorizationToken->expects($this->once())
                           ->method('getCombinationId')
                           ->willReturn($combinationId);

        $service = new IconService($this->iconImageRepository, $this->iconRepository);
        $service->injectAuthorizationToken($authorizationToken);

        $this->assertSame($combinationId, $this->extractProperty($service, 'combinationId'));
    }

    /**
     * Tests the getImageIdsByTypesAndNames method.
     * @throws ReflectionException
     * @covers ::getImageIdsByTypesAndNames
     */
    public function testGetImageIdsByTypesAndNames(): void
    {
        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);
        /* @var NamesByTypes&MockObject $namesByTypes */
        $namesByTypes = $this->createMock(NamesByTypes::class);

        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');

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

        /* @var Icon&MockObject $icon1 */
        $icon1 = $this->createMock(Icon::class);
        $icon1->expects($this->any())
              ->method('getImage')
              ->willReturn($image1);

        /* @var Icon&MockObject $icon2 */
        $icon2 = $this->createMock(Icon::class);
        $icon2->expects($this->any())
              ->method('getImage')
              ->willReturn($image2);

        /* @var Icon&MockObject $icon3 */
        $icon3 = $this->createMock(Icon::class);
        $icon3->expects($this->any())
              ->method('getImage')
              ->willReturn($image1);

        $icons = [$icon1, $icon2, $icon3];
        $expectedResult = [$id1, $id2];

        $this->iconRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with(
                                 $this->identicalTo($combinationId),
                                 $this->identicalTo($namesByTypes)
                             )
                             ->willReturn($icons);

        $service = new IconService($this->iconImageRepository, $this->iconRepository);
        $this->injectProperty($service, 'combinationId', $combinationId);

        $result = $service->getImageIdsByTypesAndNames($namesByTypes);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getTypesAndNamesByImageIds method.
     * @covers ::getTypesAndNamesByImageIds
     */
    public function testGetTypesAndNamesByImageIds(): void
    {
        /* @var Icon&MockObject $icon1 */
        $icon1 = $this->createMock(Icon::class);
        $icon1->expects($this->once())
              ->method('getType')
              ->willReturn('abc');
        $icon1->expects($this->once())
              ->method('getName')
              ->willReturn('def');

        /* @var Icon&MockObject $icon2 */
        $icon2 = $this->createMock(Icon::class);
        $icon2->expects($this->once())
              ->method('getType')
              ->willReturn('ghi');
        $icon2->expects($this->once())
              ->method('getName')
              ->willReturn('jkl');

        $imageIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];
        $icons = [$icon1, $icon2];

        $expectedResult = new NamesByTypes();
        $expectedResult->addName('abc', 'def')
                       ->addName('ghi', 'jkl');

        /* @var IconService&MockObject $iconService */
        $iconService = $this->getMockBuilder(IconService::class)
                            ->onlyMethods(['getIconsByImageIds'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $iconService->expects($this->once())
                    ->method('getIconsByImageIds')
                    ->with($this->identicalTo($imageIds))
                    ->willReturn($icons);

        $result = $iconService->getTypesAndNamesByImageIds($imageIds);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getIconsByImageIds method.
     * @throws ReflectionException
     * @covers ::getIconsByImageIds
     */
    public function testGetIconsByImageIds(): void
    {
        $imageIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];
        $icons = [
            $this->createMock(Icon::class),
            $this->createMock(Icon::class),
        ];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);

        $this->iconRepository->expects($this->once())
                             ->method('findByImageIds')
                             ->with($this->identicalTo($combinationId), $this->identicalTo($imageIds))
                             ->willReturn($icons);

        $service = new IconService($this->iconImageRepository, $this->iconRepository);
        $this->injectProperty($service, 'combinationId', $combinationId);

        $result = $service->getIconsByImageIds($imageIds);

        $this->assertEquals($icons, $result);
    }

    /**
     * Tests the getIconFilesByHashes method.
     * @covers ::getImagesByIds
     */
    public function testGetIconFilesByHashes(): void
    {
        $imageIds = [
            $this->createMock(UuidInterface::class),
            $this->createMock(UuidInterface::class),
        ];
        $iconFiles = [
            $this->createMock(IconImage::class),
            $this->createMock(IconImage::class),
        ];

        $this->iconImageRepository->expects($this->once())
                                  ->method('findByIds')
                                  ->with($this->identicalTo($imageIds))
                                  ->willReturn($iconFiles);

        $service = new IconService($this->iconImageRepository, $this->iconRepository);
        $result = $service->getImagesByIds($imageIds);

        $this->assertSame($iconFiles, $result);
    }
}
