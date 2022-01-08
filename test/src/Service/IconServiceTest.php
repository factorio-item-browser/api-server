<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\Api\Server\Service;

use FactorioItemBrowser\Api\Database\Collection\NamesByTypes;
use FactorioItemBrowser\Api\Database\Entity\Icon;
use FactorioItemBrowser\Api\Database\Entity\IconImage;
use FactorioItemBrowser\Api\Database\Repository\IconImageRepository;
use FactorioItemBrowser\Api\Database\Repository\IconRepository;
use FactorioItemBrowser\Api\Server\Service\IconService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The PHPUnit test of the IconService class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\Api\Server\Service\IconService
 */
class IconServiceTest extends TestCase
{
    /** @var IconImageRepository&MockObject */
    private IconImageRepository $iconImageRepository;
    /** @var IconRepository&MockObject */
    private IconRepository $iconRepository;
    private UuidInterface $combinationId;

    protected function setUp(): void
    {
        $this->iconImageRepository = $this->createMock(IconImageRepository::class);
        $this->iconRepository = $this->createMock(IconRepository::class);
        $this->combinationId = Uuid::fromString('2f4a45fa-a509-a9d1-aae6-ffcf984a7a76');
    }

    /**
     * @param array<string> $mockedMethods
     * @return IconService&MockObject
     */
    private function createInstance(array $mockedMethods = []): IconService
    {
        $instance = $this->getMockBuilder(IconService::class)
                         ->disableProxyingToOriginalMethods()
                         ->onlyMethods($mockedMethods)
                         ->setConstructorArgs([
                             $this->iconImageRepository,
                             $this->iconRepository,
                         ])
                         ->getMock();
        $instance->setCombinationId($this->combinationId);
        return $instance;
    }

    public function testGetImageIdsByTypesAndNames(): void
    {
        $namesByTypes = $this->createMock(NamesByTypes::class);

        $id1 = Uuid::fromString('999a23e4-addb-4821-91b5-1adf0971f6f4');
        $id2 = Uuid::fromString('db700367-c38d-437f-aa12-9cdedb63faa4');

        $image1 = new IconImage();
        $image1->setId($id1);
        $image2 = new IconImage();
        $image2->setId($id2);

        $icon1 = new Icon();
        $icon1->setImage($image1);
        $icon2 = new Icon();
        $icon2->setImage($image2);
        $icon3 = new Icon();
        $icon3->setImage($image1);

        $icons = [$icon1, $icon2, $icon3];
        $expectedResult = [$id1, $id2];

        $this->iconRepository->expects($this->once())
                             ->method('findByTypesAndNames')
                             ->with(
                                 $this->identicalTo($this->combinationId),
                                 $this->identicalTo($namesByTypes)
                             )
                             ->willReturn($icons);

        $instance = $this->createInstance();
        $result = $instance->getImageIdsByTypesAndNames($namesByTypes);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetTypesAndNamesByImageIds(): void
    {
        $icon1 = new Icon();
        $icon1->setType('abc')
              ->setName('def');

        $icon2 = new Icon();
        $icon2->setType('ghi')
              ->setName('jkl');

        $imageIds = [
            Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1'),
            Uuid::fromString('24db0d5a-a933-4e46-bb5a-0b7d88c6272e'),
        ];
        $icons = [$icon1, $icon2];

        $expectedResult = new NamesByTypes();
        $expectedResult->addName('abc', 'def')
                       ->addName('ghi', 'jkl');

        $instance = $this->createInstance(['getIconsByImageIds']);
        $instance->expects($this->once())
                 ->method('getIconsByImageIds')
                 ->with($this->identicalTo($imageIds))
                 ->willReturn($icons);

        $result = $instance->getTypesAndNamesByImageIds($imageIds);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetIconsByImageIds(): void
    {
        $imageIds = [
            Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1'),
            Uuid::fromString('24db0d5a-a933-4e46-bb5a-0b7d88c6272e'),
        ];
        $icons = [
            $this->createMock(Icon::class),
            $this->createMock(Icon::class),
        ];

        $this->iconRepository->expects($this->once())
                             ->method('findByImageIds')
                             ->with($this->identicalTo($this->combinationId), $this->identicalTo($imageIds))
                             ->willReturn($icons);

        $instance = $this->createInstance();
        $result = $instance->getIconsByImageIds($imageIds);

        $this->assertEquals($icons, $result);
    }

    public function testGetIconFilesByHashes(): void
    {
        $imageIds = [
            Uuid::fromString('11b19ed3-e772-44b1-9938-2cca1c63c7a1'),
            Uuid::fromString('24db0d5a-a933-4e46-bb5a-0b7d88c6272e'),
        ];
        $iconFiles = [
            $this->createMock(IconImage::class),
            $this->createMock(IconImage::class),
        ];

        $this->iconImageRepository->expects($this->once())
                                  ->method('findByIds')
                                  ->with($this->identicalTo($imageIds))
                                  ->willReturn($iconFiles);

        $instance = $this->createInstance();
        $result = $instance->getImagesByIds($imageIds);

        $this->assertSame($iconFiles, $result);
    }
}
